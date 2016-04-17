#!/usr/bin/env python

# modules
import exceptions
import getopt
import hashlib
import httplib
import json
import logging
import logging.handlers
import os
import random
import signal
import socket
import subprocess
import sys
import traceback
import urllib


# global variables
host = "dispatcher.twindb.com"
proto = "http"
api_dir = ""
debug = False
debug_http = True
init_config = "/etc/twindb.cfg"
ssh_port = 4194
time_zone = "UTC"
api_email = "api@twindb.com"


def usage():
    print("""Usage:
twindb-register-storage.py [-g] <registration code>

    <code>              Register this storage server in TwinDB.
                        <code> is string like 7b90ae21ac642f2f8fc0a285c4789147
                        Find your code on https://console.twindb.com/?get_code
    -g                  Print debug message.
""")
    return


def check_env():
    """
    Checks the enviroment if it's OK to start TwinDB agent
    :return: True - if the enviroment is OK
    Exits if the environment is not OK
    """
    logger.debug("Checking enviroment")
    if os.getuid() != 0:
        exit_on_error("twindb-register-storage.py must be run by root")
    logger.debug("Enviroment is OK")
    return True


def get_response(request):
    """
    Sends HTTP POST request to TwinDB dispatcher
    It converts python data structure in "data" variable into JSON string,
    then encrypts it and then sends as variable "data" in HTTP request
    :param request: Data structure with variables
    :return: String with body of HTTP response. None    - if error happened or empty response
    """
    uri = "api.php"
    response_body = None
    url = proto + "://" + host + "/" + api_dir + "/" + uri
    logger.debug("Enter get_response(url=" + url + ") with request:")
    logger.debug(request)
    if proto == "http":
        conn = httplib.HTTPConnection(host)
    elif proto == "https":
        conn = httplib.HTTPSConnection(host)
    else:
        logger.error("Unsupported protocol " + proto)
        return None
    http_response = ""
    try:
        conn.connect()
        logger.debug("Sending to " + host + ":")
        logger.debug(request)
        data_json = json.dumps(request)
        data_json_enc = data_json
        data_json_enc_urlenc = urllib.urlencode({'data': data_json_enc})
        conn.putrequest('POST', "/" + api_dir + "/" + uri)
        headers = {'Content-Length': "%d" % (len(data_json_enc_urlenc)),
                   'Content-Type': 'application/x-www-form-urlencoded'}
        for k in headers:
            conn.putheader(k, headers[k])
        conn.endheaders()
        conn.send(data_json_enc_urlenc)
        http_response = conn.getresponse()
        if http_response.status == 200:
            response_body = http_response.read()
            logger.debug("Response from server: %s" % response_body)
            if not response_body:
                return None
            url = "%(proto)s://%(host)s/%(api_dir)s/%(uri)s" % {
                "proto": proto,
                "host": host,
                "api_dir": api_dir,
                "uri": uri
            }
            logger.debug("Response from %(url)s : %(resp)s" % {
                "url": url,
                "resp": response_body
            })
        else:
            logger.error("HTTP error %d %s" % (http_response.status, http_response.reason))
    except socket.error as err:
        logger.error("Exception while making request " + url)
        logger.error(err)
        logger.error("Please check that DNS server is reachable and works")
        return None
    except exceptions.KeyError as err:
        logger.error("Failed to decode response from server %s" % http_response)
        logger.error("Could not find key %s" % err)
        return None
    finally:
        conn.close()
    return response_body


def register(code):
    logger.info("Registering TwinDB storage server with code %s" % code)
    # Read avaiable disk space
    cmd = "df -k /var/twindb-sftp/ -P "
    cmd += "| awk '{ print $4 }'"
    cmd += "| grep -v Available"
    cmd += "| head -1"
    try:
        p = subprocess.Popen(cmd, stdout=subprocess.PIPE, shell=True)
        cout, cerr = p.communicate()
        cout = cout.rstrip('\n')
        logger.debug("Getting available space:")
        logger.debug("Command: %s" % cmd)
        logger.debug("STDOUT: %s" % cout)
        logger.debug("STDERR: %s" % cerr)
        size = int(cout) * 1024
    except OSError as err:
        logger.error("Failed to start command %s: %s" % (cmd, err))
        return None
    # Read local ip addresses
    cmd = "ip addr"
    cmd += "| grep -w inet"
    cmd += "| awk '{ print $2}'"
    cmd += "| awk -F/ '{ print $1}'"
    try:
        p = subprocess.Popen(cmd, stdout=subprocess.PIPE, shell=True)
        local_ip = list()
        cout = p.communicate()[0]
        for row in cout.split("\n"):
            row = row.rstrip('\n')
            if row != "127.0.0.1":
                local_ip.append(row)
    except OSError as err:
        logger.error("Failed to execute command %s: %s" % (cmd, err))
        return None
    name = os.uname()[1].strip()  # un[1] is a hostname
    data = {
        "type": "register_storage",
        "params": {
            "reg_code": code,
            "name": name,
            "size": size,
            "local_ip": local_ip
        }
    }
    response = get_response(data)
    if response:
        jd = json.JSONDecoder()
        r = jd.decode(response)
        logger.debug(r)
        if r["success"]:
            logger.info("Received successful response to register the storage server.")
            logger.info("The storage server successfully registered in TwinDB")
            if "user_id" in r:
                logger.info("Creating local user user_id_%s" % r["user_id"])
                if create_user(r["user_id"], r["ssh_keys"]):
                    data = {
                        "type": "register_storage_confirm",
                        "params": {
                            "reg_code": code,
                            "storage_id": r["storage_id"]
                        }
                    }
                    confirm_storage_registration(data)
                    logger.info("Success")
                else:
                    logger.info("Failure")
                    sys.exit(2)
        else:
            logger.error("Failed to register the server.")
            if "error" in r:
                logger.error(r["error"])
            sys.exit(2)
        del jd
    else:
        exit_on_error("Empty response from server")
    return True


def create_user(user_id, ssh_keys):
    user_id = int(user_id)
    h = hashlib.md5()
    h.update(str(random.randint(0, 100 * 1000 * 1000)))
    password = h.hexdigest()
    username = "user_id_%d" % user_id
    cmd = [
        "twindb-add_chroot_user",
        username,
        password
    ]
    try:
        p = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        cout, cerr = p.communicate()
        if p.returncode == 0:
            # Add ssh keys
            f = open("/var/twindb-sftp/user_id_%d/home/.ssh/authorized_keys" % user_id, "a")
            for key in ssh_keys:
                f.write(key)
            f.close()
        else:
            logger.error("Failed to create user %s" % username)
            logger.error("STDOUT: %s" % cout.decode("utf-8"))
            logger.error("STDERR: %s" % cerr.decode("utf-8"))
            return False
    except OSError as err:
        logger.error("Failed to execute %r: %s" % (cmd, err))
        exit_on_error("Failed to create user user_id_%d" % user_id)
    return True


def confirm_storage_registration(data):
    response = get_response(data)
    if response:
        jd = json.JSONDecoder()
        r = jd.decode(response)
        logger.debug(r)
        if r["success"]:
            logger.info("Received successful response from dispatcher")
        else:
            logger.error("Failed to register the server.")
            if "errors" in r:
                logger.error(r["errors"])
            sys.exit(2)
    else:
        exit_on_error("Empty response from server")
    return True


# Cleans up when TwinDB agent exists

def cleanup(signum, frame):
    global logger

    if frame:
        # just to mute lint
        pass

    logger.info("Cleaning up on signal " + str(signum))
    sys.exit(0)


# Reports error removes pid and exits

def exit_on_error(msg):
    logger.error(msg)
    sys.exit(2)


def setup_logging():
    global logger

    ch = logging.StreamHandler()
    sh = logging.handlers.SysLogHandler()
    fmt_str = '%(name)s: %(levelname)7s: %(funcName)s(): line %(lineno)d: %(message)s'
    sfmt = logging.Formatter(fmt_str)
    cfmt = logging.Formatter("%(asctime)s: " + fmt_str)
    ch.setFormatter(cfmt)
    sh.setFormatter(sfmt)
    logger.addHandler(sh)
    logger.addHandler(ch)
    logger.setLevel(logging.INFO)
    # syslog handler shouldn't log DEBUG messages
    sh.setLevel(logging.INFO)


# Main function
# Parses options, creates log class etc

def main():
    # before we do *anything* we must ensure server_id is generated or read from config
    global logger
    global host

    setup_logging()

    # Signal handlers
    signal.signal(signal.SIGTERM, cleanup)
    signal.signal(signal.SIGINT, cleanup)

    opts = None
    try:
        opts, args = getopt.getopt(sys.argv[1:], 'd:gh', ['help'])
    except getopt.GetoptError as err:
        logger.error(err)
        exit_on_error(traceback.format_exc())
    optind = len(sys.argv)
    # Set options first
    for opt, arg in opts:
        if opt == '-d':
            optind -= 2
            host = arg
        elif opt == '-g':
            optind -= 1
            logger.setLevel(logging.DEBUG)
        elif opt == '--dispatcher':
            optind -= 2
            host = arg
        elif opt in ["-h", "--help"]:
            optind -= 1
            usage()
            sys.exit(0)
        else:
            usage()
            sys.exit(2)
    logger.debug("optind = %d, len(sys.argv) = %d" % (optind, len(sys.argv)))
    if optind == 2:
        regcode = sys.argv[len(sys.argv) - 1]
    else:
        usage()
        sys.exit(0)
    register(regcode)


if __name__ == "__main__":
    logger = logging.getLogger("twindb")
    main()
