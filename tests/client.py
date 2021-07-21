
import logging

from jsonrpcclient.clients.http_client import HTTPClient
from jsonrpcclient.requests import Request, Notification


client = HTTPClient("http://127.0.0.1:5001/handler.php")
response = client.send([Request("server.ping"), Notification("system.ping"), Request("server.get_registered_methods")])

for data in response.data:
    if data.ok:
        print("{}: {}".format(data.id, data.result))
    else:
        logging.error("%d: %s", data.id, data.message)
