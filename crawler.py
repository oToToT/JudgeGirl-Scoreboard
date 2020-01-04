#!/usr/bin/env python3
import requests
import sys
import json
import argparse

def get_submissions(cid):
    """
    get submisisions from JudgeGirl
    """
    url = f'https://judgegirl.csie.org/api/submission?cid={cid}&limit=20'
    id_st = set()
    data = []
    page_id = 1

    while True:
        tmp_data = requests.get(url + "&page={}".format(page_id), verify=False).json()
        if not tmp_data:
            break
        for submission in tmp_data:
            if submission['sid'] not in id_st:
                data.append(submission)
                id_st.add(submission['sid'])
        page_id += 1

    return data

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Crawl submissions from JudgeGirl as JSON')
    parser.add_argument('CID', type=int, help='Contest ID of JudgeGirl')
    args = parser.parse_args()
    print(json.dumps(get_submissions(args.CID)[::-1]))
