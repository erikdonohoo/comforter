#!/usr/bin/python
import json
import sys

if len(sys.argv) > 2:
    data = json.load(open(sys.argv[1]))
    object_list = sys.argv[2].split('.')
    for x in object_list:
        data = data[x]
    print data
