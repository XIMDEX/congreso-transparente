from bs4 import BeautifulSoup
from zipfile import ZipFile
import requests
import os
import re

ACTUAL_TERM = 10
MEDIA_ROOT = '/home/pmartin/tmp/playingwithapis/scraper'

# url = raw_input("Enter an url: ") 
# r = requests.get("http://" +url)
# data = r.text
# soup = BeautifulSoup(data)
# for link in soup.find_all('a'):
#     print(link.get('href'))
# for images in soup.find_all('img'):
#     print(images.get('src')

def get_file(url, path):
    r = requests.get(url)
    f = open("%s" % (path,), 'wb')
    f.write(r.content)
    f.close()

def extract_files(pathzip, pathunzip):
    print "extract"
    basedir = os.path.dirname(pathzip)
    try:
        xmlzip =  ZipFile(pathzip, 'r')
        xmlzip.extractall(pathunzip)
        xmlzip.close()
    except:
        print "delete %s" % (pathzip)
        os.remove(pathzip)

def get_zip(xml_url, pathzip):
    print "get_zip"
    pathzips = MEDIA_ROOT + '/zips'
    if not os.path.isdir(pathzips):
        os.mkdir(pathzips)
    if not os.path.exists(pathzip):
        print "download zip"
        get_file(xml_url.encode('utf8'), pathzip)

def common_handle(url):
    try:
        print url
        indexes = re.findall('[0-9]+', url, flags=0)
        pathzip = MEDIA_ROOT + '/zips/' + ''.join(indexes)  + '.zip'
        pathxml = MEDIA_ROOT + '/zips/' + ''.join(indexes)
        get_zip(url, pathzip)
        if not os.path.isdir(pathxml):
            extract_files(pathzip, pathxml)
        if os.path.isdir(pathxml):
            for xml in os.listdir(pathxml):
                mystring = "php ../loader.php {0}"
                os.system(mystring.format(pathxml + '/' + xml))
            os.system("php ../publish-index.php")
    except Exception, e:
        print pathzip
        print e

def handle(all_flag = 1):
    if all_flag:
        base_url1 = 'http://www.congreso.es/votaciones/OpenData?sesion='
        base_url2 = '&completa=1&legislatura=' + str(ACTUAL_TERM)
        first = 148
        # last_voting = Voting.objects.latest('session')
        # if last_voting:
        #     last = last_voting.session.id
        # else:
        #     last = 88
        last = 200
        print str(first) + ' - ' + str(last)
        for i in range(first, last):
            url = base_url1 + str(i) + base_url2
            common_handle(url)
    else:
        now = datetime.now().strftime('%Y/%m/%d')
        url = VOTACIONES_URL + now
        self.get_session(url)

handle()
