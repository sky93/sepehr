#!/usr/bin/python2

# imports
import os
import os.path
import signal
import sys
import threading
import zipfile
import traceback
import time
import subprocess
import MySQLdb
import MySQLdb.cursors
import urllib2
import json
import base64
import shutil
import ntpath
import atexit

from datetime import datetime

#
# Sepehr Config Path
# To get database information
#
env_config_path = '/usr/share/nginx/sepehr/.env'

#
# Global variables
#
state = 'start'
activeList = []
torrentList = []
dbConnection = None

# Aria variables
ariaProcess = None
zipProcesses = []

# Main Config
config = {}


# Class FuncThread imported from http://softwareramblings.com/2008/06/running-functions-as-threads-in-python.html
class FuncThread(threading.Thread):
    def __init__(self, target, *args):
        self._target = target
        self._args = args
        threading.Thread.__init__(self)

    def run(self):
        self._target(*self._args)


# Function definitions
def zip_dir(zip_file, path):
    if os.path.isfile(zip_file):
        os.remove(zip_file)
    zip_h = zipfile.ZipFile(zip_file, 'w', zipfile.ZIP_STORED, True)
    for root, dirs, files in os.walk(path):
        for file_name in files:
            zip_h.write(os.path.join(root, file_name), os.path.join(root, file_name)[len(path):])
    zip_h.close()


def load_config():
    global config, env_config_path, aria_config_path
    if os.path.isfile(env_config_path) == 0:
        log(3, 'Could not locate .env file or I do not have read permission. Exiting Now . . .')
        raise SystemExit(0)
    lines = [line.rstrip('\n') for line in open(env_config_path)]
    for l in lines:
        if l == '':
            continue
        l = l.split('=')
        config[l[0]] = l[1]
    log(1, 'ENV file loaded successfully.')


def is_running(aria2_address='127.0.0.1', aria2_port=6800):
    from sys import platform as _platform
    if _platform == "linux" or _platform == "linux2":  # Linux Platform
        import socket
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        result = sock.connect_ex((aria2_address, aria2_port))
        if result == 0:
            return 1
        else:
            return 0
    # elif _platform == "darwin":
        # OS X
    # elif _platform == "win32":
        # Windows...


def send2Aria(method, params=[], first_call=False):
    global config
    jsonreq = json.dumps({'jsonrpc': '2.0', 'id': 'backPy', 'method': method, 'params': params})
    try:
        if ariaProcess.poll() is not None and first_call == 0:
            runAria2()
            cur = dbConnection.cursor()
            cur.execute("""UPDATE download_list SET state=NULL WHERE state = -1 and deleted = 0 """)
        try:
            c = urllib2.urlopen(config['ARIA_PORT'], jsonreq)
            return json.loads(c.read())
        except urllib2.HTTPError as e:
            error_message = e.read()
            log(3, error_message['error']['message'])
            return json.loads(error_message)
    except BaseException as e:
        log(3, 'Error happened when sending query to Aria2c. The Error is: %s' % e)
        log(3, 'The query is: ' + str(method) + ' params: ' + str(params))
        return None


def runAria2():
    global ariaProcess, config
    FLOG = open('arialog.log', 'w')
    FERR = open('ariaerr.log', 'w')
    try:
        ariaProcess = subprocess.Popen([
                "aria2c","--enable-rpc", "--dir=" + config['WORKING_DIRECTORY'], "--download-result=full", "--file-allocation=none",
                "--max-connection-per-server=16", "--min-split-size=1M", "--split=16", "--max-overall-download-limit=0", "--allow-overwrite=true",
                "--max-concurrent-downloads=" + str(config['MAX_CONCURRENT_DOWNLOADS']), "--max-resume-failure-tries=5", "--follow-metalink=false",
                "--bt-max-peers=16", "--bt-request-peer-speed-limit=1M", "--follow-torrent=false", "--auto-file-renaming=false", "--daemon=false"
                                       ], stdout=FLOG, stderr=FERR)
    except BaseException as e:
        log(3, 'Exception when running Aria2c subprocess. Passing the exception for now. The exception is: %s' % e)
        pass
    resp = None

    retry = 1
    while retry < 11:
        resp = send2Aria('aria2.getVersion', [], True)
        if resp is None:
            later = 'I will wait for a second and then will try again.'
            if retry == 10:
                later = 'I won\' try anymore.'
            log(3, 'Aria2 is still not started. Tried ' + str(retry) + '/10 time(s). ' + later)
            time.sleep(1)
        else:
            break
        retry += 1

    if resp is None:
        log(3, 'Could not connect to Aria2. Exiting . . .')
        sys.exit(0)


def system_diagnosis():
    global state, config
    # Zip undone requests
    dlListFetchCursor = dbConnection.cursor()
    dlListFetchCursor.execute("""SELECT id, file_name, completed_length FROM download_list WHERE state = -3 and deleted = 0""")
    row = dlListFetchCursor.fetchone()
    while row is not None:
        id = row['id']
        t = FuncThread(zip_dir, config['WORKING_DIRECTORY'] + str(id) + "_" + row['file_name'], config['TORRENT_SAVE'] + str(id))
        zipProcesses.append({'id': id, 'proc': t, 'size': row['completed_length']})
        t.start()
        print "Zipping request " + str(id) + " to " + row['file_name'].encode('utf-8')
        row = dlListFetchCursor.fetchone()

    dlListFetchCursor.execute("""UPDATE download_list SET state=NULL WHERE state = -1 and deleted = 0 """)
    state = 'stable'


def log(error_type, message):
    if error_type == 1:
        error_type = 'NOTE'
        text_color = '\033[92m'
    elif error_type == 2:
        error_type = 'WARN'
        text_color = '\033[93m'
    else:
        error_type = 'ERRO'
        text_color = '\033[91m'

    time_str = time.strftime("%d-%m-%Y %H:%M:%S", time.localtime())
    if '--color' in str(sys.argv):
        print ('\033[94m' + time_str + '\033[0m' + ' - ' + '\033[1m' + error_type + '\033[0m' + ' - ' + text_color + message + '\033[0m')
    else:
        print (time_str + ' - ' + error_type + ' - ' + message)


def main():
    log(1, 'Service started.')

    # Load global variables
    global dbConnection, activeList, torrentList, config

    # Load config file
    load_config()

    # Shows the most important config
    log(1, 'SEPEHR version: ' + config['VERSION'])
    log(1, 'Working directory: ' + config['WORKING_DIRECTORY'])
    log(1, 'Max concurrent downloads: ' + config['MAX_CONCURRENT_DOWNLOADS'])
    log(1, 'Torrent Directory: ' + config['TORRENT_DIR'])
    log(1, 'Torrent Saving Directory: ' + config['TORRENT_SAVE'])
    log(1, 'Aria2c RPC address: ' + config['ARIA_PORT'])

    # Checks if Aria2 or tuletto.py is currently running.
    if is_running():
        log(3, "Aria2c is currently running. You have to kill it first. Exiting now . . .")
        sys.exit(0)

    # Runs Aria2c (child process)
    log(1, 'Starting Aria2c . . .')
    runAria2()
    log(1, 'Aria2c successfully started.')

    # Make database Connection
    try:
        log(1, 'Connecting to database . . .')
        dbConnection = MySQLdb.connect(config['DB_HOST'], config['DB_USERNAME'], config['DB_PASSWORD'], config['DB_DATABASE'], cursorclass=MySQLdb.cursors.DictCursor, charset='utf8')
        dlListFetchCursor = dbConnection.cursor()
        dbConnection.begin()
    except BaseException as e:
        log(3, "Could not connect to database. Exiting now . . .")
        destruct()
    log(1, 'Connected to database successfully.')

    # Check for errors
    system_diagnosis()

    queue_query = """
                      SELECT min(id) as id, user_id, link, file_name, http_user, http_password, torrent, custom_headers
                      FROM download_list
                      WHERE state is null and deleted = 0
                      GROUP BY user_id
                      ORDER BY id
                  """
    
    dlListFetchCursor.execute(queue_query)
    counter = 1

    while True:
        dlListUpdateCursor = dbConnection.cursor()
        dbConnection.begin()

        # For each active ID
        for id in activeList:
            cid = "%016d" % id
            try:
                res = send2Aria( 'aria2.tellStatus', [cid, ['gid','status','completedLength', 'totalLength', 'errorCode', 'files']])
            except BaseException as e:
                continue
            if res is None:
                log(3, 'Could not get result from Aria2c. Aria2c returned null. Trying next id in active list.')
                continue
            else:
                if res['result']['status'] == 'paused':
                    log(1, 'Pause situation detected. File id: ' + cid)
                    # Update UserDB, download_list and activeList
                    try:
                        dlListUpdateCursor.execute("""UPDATE download_list SET state=-2, completed_length=%s WHERE id = %s""", (res['result']['completedLength'], id,))
                        dbConnection.commit()
                        activeList.remove(id)
                        if id in torrentList:
                            torrentList.remove(id)
                        pause_res = send2Aria('aria2.remove', [cid])
                        log(1, "File with id " + cid + " paused successfully and database got updated. Aria2 said: " + pause_res['result'])
                    except BaseException as e:
                        dbConnection.rollback()
                        log(3, "Exception in pause procedure: %s" % e)
                        traceback.print_exc()

                elif res['result']['status'] == 'error':
                    log(1, 'Error situation detected. File id: ' + cid)
                    # Update UserDB, download_list and activeList
                    try:
                        dlListUpdateCursor.execute("""UPDATE download_list SET state=%s, completed_length=%s WHERE id = %s """, (str(res['result']['errorCode']), res['result']['completedLength'], id,))
                        dbConnection.commit()
                        activeList.remove(id)
                        if id in torrentList:
                            torrentList.remove(id)

                        remove_res = send2Aria('aria2.removeDownloadResult', [cid])
                        log(1, 'File with id: ' + cid + ' removed successfully from Error List. Error is: ' + res['result']['errorCode'])
                        log(1, "Aria2 said:" + remove_res['result'])
                    except BaseException as e:
                        dbConnection.rollback()
                        log(3, "Exception in error procedure: %s" % e)
                        traceback.print_exc()

                elif res['result']['status'] == 'removed':
                    log(1, 'Removed download detected. File id: ' + cid)
                    # Update UserDB, download_list and activeList.
                    try:
                        dlListUpdateCursor.execute("""INSERT INTO credit_log (user_id, credit_change, agent) SELECT user_id, %s, user_id FROM download_list WHERE id = %s""", (str( -int (res['result']['completedLength'])), id, ))
                        dlListUpdateCursor.execute("""UPDATE download_list SET state=-3, completed_length=%s, deleted=1 WHERE id = %s """, ( res['result']['completedLength'], id,))
                        dlListUpdateCursor.execute("""UPDATE users SET credit = credit - %s WHERE id in ( SELECT user_id FROM download_list WHERE id = %s)""", (res['result']['completedLength'], id,))
                        dbConnection.commit()
                        activeList.remove(id)
                        remove_res = send2Aria('aria2.removeDownloadResult', [cid])
                        log(1, 'Removed file and credit updated successfully. File id: ' + cid)
                        log(1, "Aria2 said:" + remove_res['result'])
                        # remove file
                        if id in torrentList:
                            shutil.rmtree(config['TORRENT_SAVE'] + str(id))
                            torrentList.remove(id)
                        else:
                            os.remove(res['result']['files'][0]['path'])
                            os.remove(res['result']['files'][0]['path'] + '.aria2')

                    except BaseException as e:
                        dbConnection.rollback()
                        log(3, "Exception in remove procedure: %s" % e)
                        traceback.print_exc()

                elif res['result']['status'] == 'complete':
                    log(1, 'Completed download detected. File id: ' + cid)
                    # Update UserDB, download_list and activeList
                    try:
                        if id in torrentList:
                            dlListUpdateCursor.execute("""UPDATE download_list SET state=-3, date_completed=%s WHERE id = %s """, (datetime.now(), id,))
                        else:
                            dlListUpdateCursor.execute("""INSERT INTO credit_log ( user_id, credit_change, agent ) SELECT user_id, %s, user_id FROM download_list WHERE id = %s""", ( str( -int (res['result']['completedLength'] ) ), id, ) )
                            dlListUpdateCursor.execute("""UPDATE download_list SET state=0, date_completed=%s, completed_length=%s WHERE id = %s """, (datetime.now(), res['result']['completedLength'], id,))
                            dlListUpdateCursor.execute("""UPDATE users SET credit = credit - %s WHERE id in ( SELECT user_id FROM download_list WHERE id = %s ) """, (res['result']['completedLength'], id,))

                        dbConnection.commit()
                        activeList.remove(id)
                        try:
                            complete_res = send2Aria('aria2.removeDownloadResult', [cid])
                            log(1, 'Removed completed file and credit updated successfully. File id: ' + cid)
                            log(1, "Aria2 said:" + complete_res['result'])
                        except BaseException as e:
                            log(3, 'removeDownloadResult did not successfully executed.')
                            pass

                        if id in torrentList:
                            log(1, 'Completed file is a torrent file. I will zip it now...')
                            tempCur = dbConnection.cursor()
                            tempCur.execute("""SELECT file_name FROM download_list WHERE id = %s""", (str(id)))
                            row = tempCur.fetchone()
                            t = FuncThread(zip_dir, config['WORKING_DIRECTORY'] + str(id) + "_" + row['file_name'], config['TORRENT_SAVE'] + str(id))
                            zipProcesses.append({'id': id, 'proc': t, 'size': res['result']['completedLength']})
                            t.start()
                            log(1, 'Zipping ' + str(id) + ' to ' + row['file_name'].encode('utf-8'))
                            torrentList.remove(id)
                    except BaseException as e:
                        dbConnection.rollback()
                        log(3, "Exception in completion procedure: %s" % e)
                        traceback.print_exc()
        # End FOR
        for field in zipProcesses:  # Check for zipping processes
            if not field['proc'].isAlive():
                try:
                    dlListUpdateCursor.execute("""INSERT INTO credit_log ( user_id, credit_change, agent ) SELECT user_id, %s, user_id FROM download_list WHERE id = %s""", ( str( -int (field['size'] ) ), field['id'],))
                    dlListUpdateCursor.execute("""UPDATE download_list SET state=0, date_completed=%s, completed_length=%s WHERE id = %s """, (datetime.now(), field['size'], field['id'],))
                    dlListUpdateCursor.execute("""UPDATE users SET credit = credit - %s WHERE id in ( SELECT user_id FROM download_list WHERE id = %s ) """, ( field['size'], field['id'],))
                    dbConnection.commit()
                    shutil.rmtree(config['TORRENT_SAVE'] + str(field['id']))
                except BaseException as e:
                    dbConnection.rollback()
                    dlListUpdateCursor.execute("""UPDATE download_list SET state=32 WHERE id = %s """, field['id'])
                    dbConnection.commit()
                    log(3, "Exception in zip procedure: %s" % e)
                    traceback.print_exc()
                zipProcesses.remove(field)

        # End FOR
        # Add a new link if possible
        try:
            res = send2Aria('aria2.getGlobalStat')['result']['numActive']
        except BaseException as e:
            continue

        while int(res) < int(config['MAX_CONCURRENT_DOWNLOADS']):
            # Find next request to be processed
            row = dlListFetchCursor.fetchone()

            if row is not None:
                if row['torrent'] == 1:
                    log(1, 'Adding new torrent (' + "%016d" % row['id'] + '): ' + ntpath.basename(row['link'].encode('utf-8')))
                    # Send request to Aria2
                    torrent = base64.b64encode(open(row['link']).read())
                    if not os.path.exists( config['TORRENT_SAVE'] + str(row['id'])):
                        os.makedirs(config['TORRENT_SAVE'] + str(row['id']) )
                    try:
                        send2Aria('aria2.addTorrent', [torrent, [], {
                                  'follow-torrent': 'false',
                                  'dir': config['TORRENT_SAVE'] + str(row['id']),
                                  'seed-time': '0',
                                  'gid': str("%016d" % row['id'])
                                  }
                        ])
                        torrentList.append(row['id'])
                        log(1, 'Torrent file successfully added.')
                    except BaseException as e:
                        log(3, "Exception while adding torrent: %s" % e.message)
                        traceback.print_exc()
                        continue
                else:
                    log(1, 'Adding new url (' + "%016d" % row['id'] + '): ' + ntpath.basename(row['link'].encode('utf-8')))
                    gid = "%016d" % row['id']
                    # Send request to Aria2
                    try:
                        send2Aria('aria2.addUri', [[row['link'].encode('utf-8')], {
                                  'out': str(row['id']) + "_" + row['file_name'],
                                  'gid': gid,
                                  'header': row['custom_headers']
                                  }
                        ])
                        log(1, 'Url successfully added.')
                    except BaseException as e:
                        log(3, "Exception while adding URL: %s" % e)
                        continue

                # Update DataBase
                activeList.append(row['id'])
                try:
                    dlListUpdateCursor.execute("""UPDATE download_list SET state=-1, date_started=%s WHERE id = %s """, (datetime.now(), row['id'],))
                    dbConnection.commit()
                except BaseException as e:
                    dbConnection.rollback()
            # End if row is not None:
            else:
                dlListFetchCursor = dbConnection.cursor()
                dbConnection.begin()
                dlListFetchCursor.execute(queue_query)
                break
            try:
                res = send2Aria('aria2.getGlobalStat')['result']['numActive']
            except BaseException as e:
                continue
        # End While

        if counter % 150 == 0:  # Each 5 minutes retry download errors
            counter = 1
            try:
                log(1, 'Retrying downloads with errors.')
                dlListUpdateCursor.execute("""UPDATE download_list set state = -3 WHERE state = 32 and deleted = 0""")
                dbConnection.commit()
                dlListUpdateCursor.execute("""UPDATE download_list set state = null WHERE (state > 0 or state = -4) and deleted = 0""")
                dbConnection.commit()
            except BaseException as e:
                dbConnection.rollback()
                log(3, "Exception in retry for error links: %s" % e)
                traceback.print_exc()

            # Put junk torrents out of the queue
            for id in torrentList:
                cid = "%016d" % id
                try:
                    res = send2Aria('aria2.tellStatus', [cid, ['gid', 'status', 'connections', 'numSeeders', 'downloadSpeed', 'completedLength']])
                except BaseException as e:
                    continue
                if res is None:
                    log(3, 'Could not get result from Aria2c in torrent junk section. Aria2c returned null. Trying next id in torrent list.')
                    continue
                try:
                    if res['result']['status'] == 'active':
                        try:
                            dlListUpdateCursor = dbConnection.cursor()
                            dbConnection.begin()
                            dlListUpdateCursor.execute("""SELECT count(*) ql FROM `download_list` where state IS NULL and deleted = 0""")
                        except BaseException as e:
                            continue

                        # If there are files in queue then suspend junk torrents
                        if dlListUpdateCursor.fetchone()['ql'] > 0:
                            # If download speed and seeders of a torrent is 0 it is counted as a junk torrent
                            if res['result']['downloadSpeed'] == '0' and res['result']['numSeeders'] == '0':
                                try:
                                    dlListUpdateCursor.execute("""UPDATE download_list SET state=%s, completed_length=%s WHERE id = %s """, (str(-4), res['result']['completedLength'], id,))
                                    dbConnection.commit()
                                    activeList.remove(id)
                                    torrentList.remove(id)
                                    log(1, 'Found junk torrent. I will send them in front of the queue')
                                    try:
                                        resp_junk = send2Aria('aria2.forceRemove', [cid])
                                        log(1, 'Force removed file. Aria2 said: ' + resp_junk['result'])
                                        resp_junk1 = send2Aria('aria2.removeDownloadResult', [cid])
                                        log(1, 'Removed download result. Aria2 said: ' + resp_junk1['result'])
                                    except BaseException as e:
                                        log(3, "Exception JUNK: %s" % e)
                                    log(1, "Torrent " + res['result']['gid'] + " successfully suspended as junk" )
                                except BaseException as e:
                                    dbConnection.rollback()
                                    log(3, "Exception in error procedure: %s" % e)
                                    traceback.print_exc()

                except BaseException as e:
                    continue

        counter += 1
        sys.stdout.flush()
        time.sleep(2)
    # End While


def destruct(*args):
    try:
        log(1, 'Checking if Aria2 is still running. Wait for 5 seconds')
        time.sleep(5)
        aria2_running = is_running()
        if aria2_running:
            log(1, 'It seems that Aria2 is running so I will say goodbye to him.')
            log(1, 'Telling Aria2 to pause all downloads by force.')
            f_pause = send2Aria('aria2.forcePauseAll')
            log(1, 'Aria2 said: ' + f_pause['result'])
        else:
            log(2, 'Ops! Aria2 is not running. Someone sent 9 signal. I can only update database.')
        try:
            dlListUpdateCursor = dbConnection.cursor()
            dbConnection.begin()
        except BaseException as e:
            pass
        for id in activeList:
            cid = "%016d" % id
            if aria2_running:
                try:
                    res = send2Aria('aria2.tellStatus', [cid, ['gid', 'completedLength']])
                except BaseException as e:
                    log(3, 'Could not get Completed Length from Aria2')
                    log(3, 'Aria2 said: ' + res['result'])
                    pass
            try:
                try:
                    res
                except NameError:
                    dlListUpdateCursor.execute("""UPDATE download_list SET state=NULL WHERE id = %s """, (id,))
                else:
                    dlListUpdateCursor.execute("""UPDATE download_list SET state=NULL, completed_length=%s WHERE id = %s """, (res['result']['completedLength'], id,))
                dbConnection.commit()
                log(1, 'File ' + cid + ' paused and database updated successfully.')
            except BaseException as e:
                dbConnection.rollback()
                log(3, "Exception in pause downloads on exit: %s" % e)
                traceback.print_exc()
        if aria2_running:
            log(1, 'Sending shutdown signal to Aria2')
            shutdown_res = send2Aria('aria2.forceShutdown')
            log(1, 'Aria2 said: ' + shutdown_res['result'])
        try:
            dbConnection.close()
        except BaseException as e:
            pass
        log(2, "Okay. It's time to go. Bye.")
    finally:
        sys.exit(0)

# Clean up when exiting
signal.signal(signal.SIGINT, destruct)
signal.signal(signal.SIGTERM, destruct)
atexit.register(destruct)

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        log(3, 'Keyboard interrupt happened.')
        pass
