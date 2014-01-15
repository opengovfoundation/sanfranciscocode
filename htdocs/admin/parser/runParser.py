#!/usr/bin/env python

import sys
import os
import re
import shutil
import subprocess

BASEDIR = "."
EXCLUDED_CODES = frozenset( [ "Zoning" ] )

def runParser( codefile ):
    ok = True
    scriptPath = os.path.join( BASEDIR, "scripts", "parseCode.py" )
    inputPath = os.path.join( BASEDIR, "data", "%s.txt" % codefile ,  )
    xmlDir = os.path.join( BASEDIR, "sites", codefile, "xml" )
    try:
        subprocess.check_call( [ scriptPath, "-i", inputPath, "-x", xmlDir ] )
    except subprocess.CalledProcessError as e:
        print str( e )
        ok = False
    return ok

def getSites():
    sites = []
    sitedir = os.path.join( BASEDIR, "sites" )
    print "sitedir = ", sitedir
    files = os.listdir( sitedir )
    for fname in files:
        fpath = os.path.join( sitedir, fname )
        if os.path.isdir( fpath ):
            if fname not in EXCLUDED_CODES:
                sites.append( fname )
    return sites

if __name__ == "__main__":

    sites = getSites()
    succeeded = []
    failed = []
    for site in sites:
        if runParser( site ):
            succeeded.append( site )
        else:
            failed.append( site )

    print "Sites Succeeded: ", str( succeeded )
    print "Sites Failed: ", str( failed )
    print
    print "Number succeeded : ", len( succeeded )
    print "Number failed    : ", len( failed )
