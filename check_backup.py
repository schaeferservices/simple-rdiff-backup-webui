#!/usr/bin/env python

__author__ = 'Daniel Schäfer <code@daschaefer.de>'
__license__ = 'GPLv3'
__version__  = '0.1'

import argparse
import requests
from requests.exceptions import ConnectionError
import sys

def icinga_ok(msg):
    msg = str(msg)
    print('Backup OK - ' + msg)
    sys.exit(0)

def icinga_warning(msg):
    msg = str(msg)
    print('Backup WARNING - ' + msg)
    sys.exit(1)

def icinga_critical(msg):
    msg = str(msg)
    print('Backup CRITICAL - ' + msg)
    sys.exit(2)

def icinga_unknown(msg):
    msg = str(msg)
    print('Backup UNKNOWN - ' + msg)
    sys.exit(3)

def api_request(url):
    try:
        r = requests.get(url, timeout=10)
    except ConnectionError as e:
        print(e)
        icinga_unknown('API not responding')
    if r.status_code != 200:
        icinga_unknown('Unknown error: {}'.format(r.status_code))

    data = r.json()
    return data

def main():
    main_parser = argparse.ArgumentParser(description=""" Icinga plugin to integrate SSIT Backup """)

    main_parser.add_argument('--protocol', choices=['http', 'https'], default='http', help='Runs your system on HTTP or HTTPS? Default: HTTP.')
    main_parser.add_argument('--host', '-H', dest='host', help='Host running backup web-instance.')
    main_parser.add_argument('--port', '-P', type=int, default=80, dest='port', help='Port SSIT Backup is running on (default: 80).')

    mode = main_parser.add_mutually_exclusive_group()
    mode.add_argument('--disks', '-D', action='store_true', help='Get status of backup disks.')
    mode.add_argument('--backups', '-B', action='store_true', help='Get status of backups.')

    args = main_parser.parse_args()
    api = args.protocol + '://' + args.host + ':{}?output=json'.format(args.port)

    if args.port < 1 or args.port > 65535:
        icinga_unknown('Port has to be something between 1 and 65535.')

    state = api_request(api)

    if args.disks:
        bdsOk = list(bds for bds in state['BackupDiskStats'] if bds['state'] == "Ok")
        bdsWarning = list(bds for bds in state['BackupDiskStats'] if bds['state'] == "Warning")
        bdsCritical = list(bds for bds in state['BackupDiskStats'] if bds['state'] == "Critical")

        outputOk = ''
        outputWarning = ''
        outputCritical = ''

        if len(bdsOk) > 0:
            outputOk = '{} disks are Ok.'.format(len(bdsOk))

        if len(bdsWarning) > 0:
            outputWarning = '{} disks are Warning. '.format(len(bdsWarning))

        if len(bdsCritical) > 0:
            icinga_critical('{} disks are Critical. {}{}'.format(len(bdsCritical), outputWarning, outputOk))
        elif len(bdsWarning) > 0:
            icinga_warning('{} {}'.format(outputWarning, outputOk))
        else:
            icinga_ok(outputOk.strip())

    elif args.backups:
        bsOk = list(bs for bs in state['BackupStats'] if bs['state'] == "Ok")
        bsWarning = list(bs for bs in state['BackupStats'] if bs['state'] == "Warning")
        bsCritical = list(bs for bs in state['BackupStats'] if bs['state'] == "Critical")

        outputOk = ''
        outputWarning = ''
        outputCritical = ''

        if len(bsOk) > 0:
            outputOk = '{} backups are Ok.'.format(len(bsOk))

        if len(bsWarning) > 0:
            outputWarning = '{} backups are Warning. '.format(len(bsWarning))

        if len(bsCritical) > 0:
            icinga_critical('{} backups are Critical. {}{}'.format(len(bsCritical), outputWarning, outputOk))
        elif len(bsWarning) > 0:
            icinga_warning('{} {}'.format(outputWarning, outputOk))
        else:
            icinga_ok(outputOk.strip())

    else:
        icinga_unknown('Choose either --disks or --backups.')

if __name__ == "__main__":
    main()
