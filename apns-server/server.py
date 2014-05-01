import tornado.httpserver
import tornado.ioloop
import tornado.options
import tornado.web
from apns import APNs, Frame, Payload
import hashlib
import httplib, urllib

from tornado.options import define,options

define("port", default = 8000, help="run on the given port", type=int)

cert_file = 'xxxxx.pem'
key_file = 'xxxxx.pem'
AUTH_KEY = 'xxxxx'

apnsobj = APNs(use_sandbox=False,cert_file=cert_file, key_file=key_file)

def pushOver(msg = ''):
	conn = httplib.HTTPSConnection("api.pushover.net:443")
	conn.request("POST", "/1/messages.json",
		urllib.urlencode({
    	"token": "xxxxxxxx",
    	"user": "xxxxxxx",
    	"message": msg,
  		}), { "Content-type": "application/x-www-form-urlencoded" })
	conn.getresponse()

def pushApns(device_token,payload,repeat = 0):
	global apnsobj
	if repeat <= 3:
		try:
			apnsobj.gateway_server.send_notification(device_token, payload)
		except Exception, e:
			print e
			apnsobj.gateway_server.close_ssl()
			pushApns(device_token,payload,repeat+1)
	else:
		raise Exception('can not connect apple server')

class SendHandler(tornado.web.RequestHandler):
	def post(self):
		
		device_token = self.get_argument('device_token','')
		body = self.get_argument('body', '')
		badge = self.get_argument('badge', 1)
		sound = self.get_argument('sound','')
		auth_token = self.get_argument('auth_token','')

		if auth_token is None or hashlib.md5(device_token+AUTH_KEY).hexdigest() != auth_token :
			self.write("false")
			return False

		if device_token and badge and auth_token:
			try:
				payload = Payload(alert = body, sound = sound, badge = badge)
				pushApns(device_token,payload,repeat=0)
				self.write("true")
			except Exception, e:
				pushOver(str(e))
				self.write("false")
		else:
			self.write("false")

class MainHandler(tornado.web.RequestHandler):
	def get(self):
		self.write("I am Push Server")


if __name__ == "__main__":
	tornado.options.parse_command_line()
	app = tornado.web.Application(handlers=[
    	(r"/", MainHandler),
    	(r"/send/", SendHandler)
    ])
	http_server = tornado.httpserver.HTTPServer(app,xheaders=True) 
	http_server.listen(options.port)
	tornado.ioloop.IOLoop.instance().start()

