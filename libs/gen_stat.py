#!/usr/bin/env python3
import cids2html
import colorama as co
import os
import json

if __name__ == '__main__':
    co.init()
    
    root = os.path.join(os.path.dirname(__file__), '..')
    for f in os.listdir(os.path.join(root, 'stat')):
        if not os.path.isdir(os.path.join(root, 'stat', f)):
            continue
        config = json.load(open(os.path.join(root, 'stat', f, 'config.json')))
        print('generating ' + co.Fore.MAGENTA + config['name'] + co.Fore.RESET)
        cids2html.html_to_file(config['contests'], open(os.path.join(root, 'stat', f, 'index.html'), 'w'))
