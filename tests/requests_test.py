import requests
import json

def clean(x):
    x = x.replace("<b>", "")
    x = x.replace("</b>", "")
    x = x.replace("<br />", "")
    x = x.replace("&quot;", "\"")
    x = x.replace("&gt;", ">")
    return x

a = {"jsonrpc": "2.0", "method": "server.ping", "params": [], "id": 1}
b = {"jsonrpc": "2.0", "method": "system.ping", "params": [], "id": 2}
c = {"jsonrpc": "2.0", "method": "server.get_registered_methods", "params": [], "id": 3}
print(clean(requests.post("http://127.0.0.1:5001/handler.php", headers={"Content-Type": "application/json-rpc"}, data=json.dumps([a, b, c])).text))