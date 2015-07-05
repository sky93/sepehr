<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\User;
use main;
use nusoap_client;

class PaymentController extends Controller {
    /**
     * The registrar implementation.
     *
     * @var Registrar
     */
    protected $registrar;


	public function payment_history($user_name)
    {
        if (Auth::user()->username == $user_name || Auth::user()->role == 2) {
            $user = User::where('username', '=', $user_name)->first();

            if ($user == null)
                return view('errors.general', array('error_title' => 'ERROR 404', 'error_message' => 'This user does not exist or you do not have permission to view this user.'));

            $tracks = DB::table('payments')
                ->where('user_id', '=', $user->id)
                ->whereNotNull('verifyCode')
                ->where('verifyCode', '=', '0')
                ->get();

            $main = new main();
            return view('payment.user_history', array('main' => $main, 'user' => $user, 'tracks' => $tracks));
        }else{
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Access Denied'));
        }
    }



    public function credit_buy()
    {
        $main = new main();
        return view('payment.buy', ['main' => $main]);
    }


    public function post_credit_buy(Request $request)
    {
       if ($request->ajax()){
           function is_decimal( $val )
           {
               return is_numeric( $val ) && floor( $val ) == $val;
           }
           if (Config::get('leech.payment_type') == 'discount' && ($request['amount'] != 5 && $request['amount'] != 10 && $request['amount'] != 20 && $request['amount'] != 50 && $request['amount'] != 100)){
               return response()->json(['r' => 'e', 'm' => '1']);
           }
           if (
               is_null($request['amount'])     ||
               !is_numeric($request['amount']) ||
               $request['amount'] > 100        ||
               $request['amount'] < 5          ||
               is_float($request['amount'])    ||
               !is_decimal($request['amount'])
           ) {
               return response()->json(['r' => 'e', 'm' => '1']);
           }else{
               /*******************************************/
               if (Config::get('leech.payment_type') == 'discount'){
                   if ($request['amount'] == 5) $amount = Config::get('leech.5GB_price');
                   else if ($request['amount'] == 10) $amount = Config::get('leech.10GB_price');
                   else if ($request['amount'] == 20) $amount = Config::get('leech.20GB_price');
                   else if ($request['amount'] == 50) $amount = Config::get('leech.50GB_price');
                   else if ($request['amount'] == 100) $amount = Config::get('leech.100GB_price');
                   else return response()->json(['r' => 'e', 'm' => '1']);
               }else{
                   $amount = $request['amount'] * Config::get('leech.credit_unit');
               }
               $id = DB::table('payments')->insertGetId([
                       'user_id' => Auth::user()->id,
                       'amount' => $amount,
                       'credit' => $request['amount']
               ]);

               try {
                   $client = new nusoap_client(Config::get('leech.soap_client'));
               } catch (Exception $e) {
                   return response()->json(['r' => 'e', 'm' => '2']);
               }
               $namespace=Config::get('leech.namespace');

               if ($client->getError()) {
                   return response()->json(['r' => 'e', 'm' => '3']);
               }

               $parameters = [
                   'terminalId' => Config::get('leech.terminalId'),
                   'userName' => Config::get('leech.userName'),
                   'userPassword' => Config::get('leech.userPassword'),
                   'orderId' => $id,
                   'amount' => $amount,
                   'localDate' => date("ymd"),
                   'localTime' => date("His"),
                   'additionalData' => 'Sepehr (B) - ' . Auth::user()->username,
                   'callBackUrl' => asset('/buy'),
                   'payerId' => 0
               ];

               $result = $client->call('bpPayRequest', $parameters, $namespace);

               if ($client->fault) {
                   return response()->json(['r' => 'e', 'm' => '4']);
               }
               else {
                   $resultStr  = $result;

                   if ($client->getError()) {
                       return response()->json(['r' => 'e', 'm' => '5']);
                   }
                   else {
                       $res = explode (',',$resultStr);
                       $ResCode = $res[0];

                       if ($ResCode == 0) {
                           DB::table('payments')
                               ->where('id', $id)
                               ->update([
                                   'RefId' => $res[1],
                                   'ResCode1' => $res[0]
                               ]);

                           return response()->json([
                               'r' => 's',
                               'm' => '6',
                               'RefId' => $res[1],
                               'o_id' => $id,
                               't_amount' => $amount . ' ' . Config::get('leech.currency'),
                               't_credits' => $request['amount'] . ' GB'

                           ]);
                       }
                       else {
                           DB::table('payments')
                               ->where('id', $id)
                               ->update([
                                   'ResCode1' => $res[0]
                               ]);

                           return response()->json(['r' => 'e', 'm' => '7']);
                       }
                   }
               }

               /*******************************************/
           }
       }else{
           $post = true;
           $input = $request->only('RefId', 'ResCode', 'SaleOrderId', 'SaleReferenceId', 'CardHolderInfo', 'CardHolderPan');


           $payment = DB::table('payments')
               ->where('id', '=', $input['SaleOrderId'])
               ->where('RefId', '=', $input['RefId'])
               ->first();

          // var_dump($payment);
           //return;
           $main = new main();
           if ($payment == null) {
               return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'Wrong ID.', 'post' => $post]);
           } else {

               DB::table('payments')
                   ->where('id', '=', $input['SaleOrderId'])
                   ->where('RefId', '=', $input['RefId'])
                   ->update([
                       'ResCode2' => $input['ResCode'],
                       'SaleReferenceId' => $input['SaleReferenceId'],
                       'CardHolderInfo' => $input['CardHolderInfo'],
                       'CardHolderPan' => $input['CardHolderPan'],
                       'pay_time' => date('Y-m-d H:i:s', time())
                   ]);

               if ($input['ResCode'] == 0){ //we should verify

                   try {
                       $client = new nusoap_client(Config::get('leech.soap_client'));
                   } catch (Exception $e) {
                       return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'We could not verify your payment. We will refund your money.', 'post' => $post]);
                   }
                   $namespace=Config::get('leech.namespace');

                   if ($client->getError()) {
                       return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'We could not verify your payment. We will refund your money.', 'post' => $post]);
                   }

                   $parameters = [
                       'terminalId' => Config::get('leech.terminalId'),
                       'userName' => Config::get('leech.userName'),
                       'userPassword' => Config::get('leech.userPassword'),
                       'orderId' => $input['SaleOrderId'],
                       'saleOrderId' => $input['SaleOrderId'],
                       'saleReferenceId' => $input['SaleReferenceId'],
                   ];

                   // Call the SOAP method
                   $result = $client->call('bpVerifyRequest', $parameters, $namespace);

                   if ($client->fault) {
                       return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'We could not connect to bank.', 'post' => $post]);
                   }
                   else {

                       $resultStr = $result;

                       $err = $client->getError();
                       if ($err) {
                           return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'We could not connect to bank. No 2', 'post' => $post]);
                       }
                       else {
                           DB::table('payments')
                               ->where('id', '=', $input['SaleOrderId'])
                               ->where('RefId', '=', $input['RefId'])
                               ->update([
                                   'verifyCode' => $resultStr,
                               ]);
                           if ($resultStr == 0){

                               // SETTLE

                               try {
                                   $client = new nusoap_client(Config::get('leech.soap_client'));
                               } catch (Exception $e) {
                                   return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'We could not verify your payment. We will refund your money.', 'post' => $post]);
                               }
                               $namespace=Config::get('leech.namespace');

                               if ($client->getError()) {
                                   return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'We could not verify your payment. We will refund your money.', 'post' => $post]);
                               }

                               $parameters = [
                                   'terminalId' => Config::get('leech.terminalId'),
                                   'userName' => Config::get('leech.userName'),
                                   'userPassword' => Config::get('leech.userPassword'),
                                   'orderId' => $input['SaleOrderId'],
                                   'saleOrderId' => $input['SaleOrderId'],
                                   'saleReferenceId' => $input['SaleReferenceId']
                                ];

                               $result = $client->call('bpSettleRequest', $parameters, $namespace);

                               if ($client->fault) {
                                   $res = -1;
                               }else {
                                   $res = $result;
                               }
                               DB::table('payments')
                                   ->where('id', '=', $input['SaleOrderId'])
                                   ->where('RefId', '=', $input['RefId'])
                                   ->update([
                                       'settleResponse' => $res,
                                   ]);

                               $user = DB::table('users')
                                   ->where('id', '=', $payment->user_id)
                                   ->first();

                               $info = [
                                   'username' => $user->username,
                                   'old.credit' => $user->credit,
                                   'refID' => $input['SaleReferenceId'],
                                   'card' => $input['CardHolderPan'],
                               ];

                               DB::table('users')
                                   ->where('id', '=', $payment->user_id)
                                   ->increment('credit', $payment->credit * 1024 * 1024 * 1024);

                               DB::table('credit_log')->insert(
                                   array(
                                       'user_id' => $payment->user_id,
                                       'credit_change' =>  $payment->credit * 1024 * 1024 * 1024,
                                       'agent' => 0,
                                   )
                               );

                               $info['new.credit'] = $info['old.credit'] + ($payment->credit * 1024 * 1024 * 1024);
                               return view('payment.buy', ['main' => $main, 'res' => 'success', 'info' => $info, 'post' => $post]);

                           }else{
                               return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'Your Payment is not verified', 'post' => $post]);
                           }
                       }
                   }

               }else{
                   return view('payment.buy', ['main' => $main, 'res' => 'error', 'error' => 'Your Payment was not successful', 'post' => $post]);
               }

           }

       }
    }


}
