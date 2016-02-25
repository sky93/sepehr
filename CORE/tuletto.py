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
import atexit
import MySQLdb
import MySQLdb.cursors
import urllib2
import json
import base64
import shutil

from datetime import datetime

#
# Global variables
#
state = 'start'
activeList = []
torrentList = []
maxConcurrentDownloads = 10
torrentDir = '/mnt/hd0/torrent/'
torrentSave = '/mnt/hd0/torrent_save/'

# MySQL variables
dbConnection = None
dbName = 'leech'
dbUser = 'root'
dbPassword = 'pass'

# Aria variables
ariaPort = 'http://127.0.0.1:6800/jsonrpc'
ariaProcess = None
zipProcesses = []
workingDirectory = '/mnt/hd0/'


# Class FuncThread imported from http://softwareramblings.com/2008/06/running-functions-as-threads-in-python.html
class FuncThread(threading.Thread):
    def __init__(self, target, *args):
        self._target = target
        self._args = args
        threading.Thread.__init__(self)

    def run(self):
        self._target(*self._args)


# Class "GracefulInterruptHandler" imported from https://gist.github.com/nonZero/2907502
class GracefulInterruptHandler(object):

    def __init__(self, sig=signal.SIGINT):
        self.sig = sig

    def __enter__(self):

        self.interrupted = False
        self.released = False

        self.original_handler = signal.getsignal(self.sig)

        def handler():
            self.release()
            self.interrupted = True

        signal.signal(self.sig, handler)

        return self

    def __exit__(self, type, value, tb):
        self.release()

    def release(self):

        if self.released:
            return False

        signal.signal(self.sig, self.original_handler)

        self.released = True

        return True

# Function definitions
def zipDir( zipFile, path ):
    if os.path.isfile(zipFile):
        os.remove(zipFile)
    ziph = zipfile.ZipFile( zipFile, 'w', zipfile.ZIP_STORED, True )
    for root, dirs, files in os.walk(path):
        for file in files:
            ziph.write(os.path.join(root, file), os.path.join(root, file)[len(path):] )
    ziph.close()
    # shutil.make_archive(zipName, 'zip', dir)


def send2Aria(method, params=[]):
    jsonreq = json.dumps({'jsonrpc': '2.0', 'id': 'backPy', 'method': method, 'params': params})
    try:
        if ariaProcess.poll() is not None:
            runAria2()
            cur = dbConnection.cursor()
            cur.execute("""UPDATE download_list SET state=NULL WHERE state = -1 and deleted = 0 """)
        try:
            c = urllib2.urlopen(ariaPort, jsonreq)
            return json.loads(c.read())
        except urllib2.HTTPError as e:
            error_message = e.read()
            log(3, error_message)
            return json.loads(error_message)
    except BaseException as e:
        raise Exception(e)


def runAria2():
    global ariaProcess
    FLOG = open('/root/arialog', 'w')
    FERR = open('/root/ariaerr', 'w')
    ariaProcess = subprocess.Popen([
            "aria2c","--enable-rpc", "--dir=" + workingDirectory, "--download-result=full", "--file-allocation=none",
            "--max-connection-per-server=16", "--min-split-size=1M", "--split=16", "--max-overall-download-limit=0",
            "--max-concurrent-downloads=" + str(maxConcurrentDownloads), "--max-resume-failure-tries=5", "--follow-metalink=false",
            "--bt-max-peers=16","--bt-request-peer-speed-limit=1M","--follow-torrent=false","--auto-file-renaming=false","--daemon=false"
                                   ], stdout=FLOG, stderr=FERR)
    i = 0
    resp = None
    while True:
        try:
            resp = send2Aria( 'aria2.getVersion', [])
        except BaseException as e:
            pass
        i += 1
        time.sleep(1)
        if not (resp is None and i < 20 ):
            break
    if i == 10:
        print "Could not connect to Aria2. Exiting..."
        exit(1)


def system_diagnosis():
    global state
    # Zip undone requests
    dlListFetchCursor = dbConnection.cursor()
    dlListFetchCursor.execute("""SELECT id, file_name, completed_length FROM download_list WHERE state = -3 and deleted = 0""")
    row = dlListFetchCursor.fetchone()
    while row is not None:
        id = row['id']
        t = FuncThread(zipDir, workingDirectory + str(id) + "_" + row['file_name'], torrentSave + str(id))
        zipProcesses.append({'id': id, 'proc': t, 'size': row['completed_length']})
        t.start()
        print "Zipping request " + str(id) + " to " + row['file_name'].encode('utf-8')
        row = dlListFetchCursor.fetchone()

    dlListFetchCursor.execute("""UPDATE download_list SET state=NULL WHERE state = -1 and deleted = 0 """)
    state = 'stable'


def log(error_type, message):
    if error_type == 1:
        error_type = 'NOTE'
    elif error_type == 2:
        error_type = 'WARN'
    else:
        error_type = 'ERRO'

    print (time.ctime() + ' - ' + error_type + ' - ' + message)


def main():
    print ("Service started")

    global dbConnection, dbUser, dbPassword, dbName, activeList, torrentList, torrentDir, torrentSave

    # Runs Aria2c (child process)
    runAria2()

    # dbConnection
    dbConnection = MySQLdb.connect("localhost", dbUser, dbPassword, dbName, cursorclass=MySQLdb.cursors.DictCursor, charset='utf8')
    dlListFetchCursor = dbConnection.cursor()
    dbConnection.begin()

    # Check for errors
    system_diagnosis()

    dlListFetchCursor.execute("""SELECT min(id) as id, user_id, link, file_name, http_user, http_password, torrent, custom_headers
                                  FROM download_list
                                  WHERE state is null and deleted = 0
                                  GROUP BY user_id
                                  ORDER BY id
                               """)
    counter = 1
    with GracefulInterruptHandler(signal.SIGINT) as h1:
        with GracefulInterruptHandler(signal.SIGTERM) as h2:
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
                    if res['result']['status'] == 'paused':
                        log(1, 'Pause situation detected.')
                        # Update UserDB, download_list and activeList
                        try:
                            dlListUpdateCursor.execute("""UPDATE download_list SET state=-2, completed_length=%s WHERE id = %s """, (res['result']['completedLength'], id,))
                            dbConnection.commit()
                            activeList.remove(id)
                            pause_res = send2Aria('aria2.remove', [cid])
                            log(1, "File with id " + id + " paused successfully and database got updated. Aria2 Message: " + pause_res['result'])
                        except BaseException as e:
                            dbConnection.rollback()
                            log(3, "Exception in pause procedure: %s" % e)
                            traceback.print_exc()

                    elif res['result']['status'] == 'error':
                        # Update UserDB, download_list and activeList
                        try:
                            dlListUpdateCursor.execute("""UPDATE download_list SET state=%s, completed_length=%s WHERE id = %s """, ( str(res['result']['errorCode']), res['result']['completedLength'], id,))
                            dbConnection.commit()
                            activeList.remove(id)
                            send2Aria('aria2.removeDownloadResult', [cid])
                            print "Request " + res['result']['gid'] + " got an error with code: " + res['result']['errorCode']
                        except BaseException as e:
                            dbConnection.rollback()
                            print "Exception in error procedure: %s" % e
                            traceback.print_exc()

                    elif res['result']['status'] == 'removed':
                        # Update UserDB, download_list and activeList.
                        try:
                            dlListUpdateCursor.execute("""INSERT INTO credit_log (user_id, credit_change, agent) SELECT user_id, %s, user_id FROM download_list WHERE id = %s""", (str( -int (res['result']['completedLength'])), id, ))
                            dlListUpdateCursor.execute("""UPDATE download_list SET state=-3, completed_length=%s, deleted=1 WHERE id = %s """, ( res['result']['completedLength'], id,))
                            dlListUpdateCursor.execute("""UPDATE users SET credit = credit - %s WHERE id in ( SELECT user_id FROM download_list WHERE id = %s)""", (res['result']['completedLength'], id,))
                            dbConnection.commit()
                            activeList.remove(id)
                            send2Aria('aria2.removeDownloadResult', [cid])
                            print "Request " + res['result']['gid'] + " canceled"
                            # remove file
                            if id in torrentList:
                                shutil.rmtree(torrentSave + str(id))
                            else:
                                os.remove(res['result']['files'][0]['path'])
                                os.remove(res['result']['files'][0]['path'] + '.aria2')
                        except BaseException as e:
                            dbConnection.rollback()
                            print "Exception in remove procedure: %s" % e
                            traceback.print_exc()

                    elif res['result']['status'] == 'complete':
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
                                send2Aria('aria2.removeDownloadResult', [cid])
                            except BaseException as e:
                                pass
                            print "Request " + res['result']['gid'] + " completed."
                            if id in torrentList:
                                tempCur = dbConnection.cursor()
                                tempCur.execute("""SELECT file_name FROM download_list WHERE id = %s""", (str(id)))
                                row = tempCur.fetchone()
                                t = FuncThread(zipDir, workingDirectory + str(id) + "_" + row['file_name'], torrentSave + str(id))
                                zipProcesses.append({'id': id, 'proc': t, 'size': res['result']['completedLength']})
                                t.start()
                                print "Zipping request " + str(id) + " to " + row['file_name'].encode('utf-8')
                                torrentList.remove( id )
                        except BaseException as e:
                            dbConnection.rollback()
                            print "Exception in completion procedure: %s" % e
                            traceback.print_exc()
                # End FOR
                for field in zipProcesses:  # Check for zipping processes
                    if not field['proc'].isAlive():
                        try:
                            dlListUpdateCursor.execute("""INSERT INTO credit_log ( user_id, credit_change, agent ) SELECT user_id, %s, user_id FROM download_list WHERE id = %s""", ( str( -int (field['size'] ) ), field['id'],))
                            dlListUpdateCursor.execute("""UPDATE download_list SET state=0, date_completed=%s, completed_length=%s WHERE id = %s """, (datetime.now(), field['size'], field['id'],))
                            dlListUpdateCursor.execute("""UPDATE users SET credit = credit - %s WHERE id in ( SELECT user_id FROM download_list WHERE id = %s ) """, ( field['size'], field['id'],))
                            dbConnection.commit()
                            shutil.rmtree(torrentSave + str(field['id']))
                        except BaseException as e:
                            dbConnection.rollback()
                            dlListUpdateCursor.execute("""UPDATE download_list SET state=32 WHERE id = %s """, field['id'])
                            dbConnection.commit()
                            print "Exception in zip procedure: %s" % e
                            traceback.print_exc()
                        zipProcesses.remove(field)

                # End FOR
                # Add a new link if possible
                try:
                    res = send2Aria('aria2.getGlobalStat')['result']['numActive']
                except BaseException as e:
                    continue

                while int(res) < maxConcurrentDownloads:
                    # Find next request to be processed
                    row = dlListFetchCursor.fetchone()

                    if row is not None:
                        if row['torrent'] == 1:
                            print "Adding new torrent: " + row['link'].encode('utf-8')
                            print "With gid: " + "%016d" % row['id']
                            # Send request to Aria2
                            torrent = base64.b64encode(open(row['link']).read())
                            if not os.path.exists( torrentSave + str(row['id'])):
                                os.makedirs(torrentSave + str(row['id']) )
                            try:
                                send2Aria('aria2.addTorrent', [torrent, [], {
                                          'follow-torrent': 'false',
                                          'dir': torrentSave + str(row['id']),
                                          'seed-time': '0',
                                          'gid': "%016d" % row['id']
                                          }
                                ])
                                torrentList.append(row['id'])
                            except BaseException as e:
                                print "Exception while adding torrent: %s" % e.message
                                traceback.print_exc()
                                continue
                        else:
                            print "Adding new url: " + row['link'].encode('utf-8')
                            gid = "%016d" % row['id']
                            print "With gid: " + gid
                            # Send request to Aria2
                            try:
                                send2Aria('aria2.addUri', [[row['link'].encode('utf-8')], {
                                          'out': str(row['id']) + "_" + row['file_name'],
                                          'gid': gid,
                                          'header': row['custom_headers']
                                          }
                                ])
                            except BaseException as e:
                                print "Exception while adding URL: %s" % e
                                traceback.print_exc()
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
                        dlListFetchCursor.execute("""SELECT min(id) as id, user_id, link, file_name, http_user, http_password, torrent, custom_headers
                                                      FROM download_list
                                                      WHERE state is null and deleted = 0
                                                      GROUP BY user_id
                                                      ORDER BY id
                                                   """)
                        break
                    try:
                        res = send2Aria('aria2.getGlobalStat')['result']['numActive']
                    except BaseException as e:
                        continue
                # End While

                if counter % 150 == 0:  # Each 5 minutes retry download errors
                    counter = 1
                    try:
                        dlListUpdateCursor.execute("""UPDATE download_list set state = -3 WHERE state = 32 and deleted = 0""")
                        dbConnection.commit()
                        dlListUpdateCursor.execute("""UPDATE download_list set state = null WHERE (state > 0 or state = -4) and deleted = 0""")
                        dbConnection.commit()
                    except BaseException as e:
                        dbConnection.rollback()
                        print "Exception in retry for error links: %s" % e
                        traceback.print_exc()
                    for id in torrentList:   # Put junk torrents in queue
                        cid = "%016d" % id
                        try:
                            res = send2Aria('aria2.tellStatus', [cid, ['gid', 'status', 'connections', 'numSeeders', 'downloadSpeed', 'completedLength']])
                        except BaseException as e:
                            continue
                        if res['result']['status'] == 'active':
                            try:
                                dlListUpdateCursor = dbConnection.cursor()
                                dbConnection.begin()
                                dlListUpdateCursor.execute("""SELECT count(*) ql FROM `download_list` where state IS NULL and deleted = 0""")
                            except BaseException as e:
                                continue

                            # If there are files in queue then suspend junk torrents
                            if dlListUpdateCursor.fetchone()['ql'] > 0:
                                # If dl speed and seeders of a torrent is 0 it is junk
                                if res['result']['downloadSpeed'] == '54850' and res['result']['numSeeders'] == '0':
                                    try:
                                        dlListUpdateCursor.execute("""UPDATE download_list SET state=%s, completed_length=%s WHERE id = %s """, (str(-4), res['result']['completedLength'], id,))
                                        dbConnection.commit()
                                        activeList.remove(id)
                                        send2Aria('aria2.remove', [cid])
                                        try:
                                            resp = send2Aria('aria2.removeDownloadResult', '[ "' + cid + '" ]')
                                            print resp
                                        except BaseException as e:
                                            print "Exception JUNK: %s" % e
                                        torrentList.remove(id)
                                        print "Request " + res['result']['gid'] + " suspended for being junk"
                                        send2Aria('aria2.removeDownloadResult', [cid])
                                    except BaseException as e:
                                        dbConnection.rollback()
                                        print "Exception in error procedure: %s" % e
                                        traceback.print_exc()

                if h1.interrupted or h2.interrupted:
                    print "Exiting. Bye :)"
                    break

                counter += 1
                sys.stdout.flush()
                time.sleep(2)
            # End While

@atexit.register
def destruct():
    dlListUpdateCursor = dbConnection.cursor()
    dbConnection.begin()
    for id in activeList:
        cid = "%016d" % id
        try:
            send2Aria('aria2.pauseAll')
            res = send2Aria('aria2.tellStatus', [cid, ['gid','completedLength']])
        except BaseException as e:
            pass
        try:
            dlListUpdateCursor.execute("""UPDATE download_list SET state=NULL, completed_length=%s WHERE id = %s """, (res['result']['completedLength'], id,))
            dbConnection.commit()
            print "Request " + cid + " paused"
        except BaseException as e:
            dbConnection.rollback()
            print "Exception in pause downloads on exit: %s" % e
            traceback.print_exc()

    send2Aria('aria2.shutdown')
    dbConnection.close()

if __name__ == "__main__":
    main()
