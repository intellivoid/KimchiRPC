import requests
import json

def clean(x):
    x = x.replace("<b>", "")
    x = x.replace("</b>", "")
    x = x.replace("<br />", "")
    x = x.replace("&quot;", "\"")
    x = x.replace("&gt;", ">")
    return x

a = [{"jsonrpc":"2.0","method":"server.ping","id":1},{"jsonrpc":"2.0","method":"system.ping"},{"jsonrpc":"2.0","method":"server.get_registered_methods","id":2}]
print(clean(requests.post("http://127.0.0.1:5001/handler.php", headers={"Content-Type": "application/json-rpc"}, data=json.dumps(a)).text))