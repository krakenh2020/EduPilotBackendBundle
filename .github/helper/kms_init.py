import requests
from requests.packages.urllib3.exceptions import InsecureRequestWarning
requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

agent_url = 'http://localhost:8082' # university agent

print('Check connection ...')
r = requests.get(agent_url + '/connections', verify=False)
if r.status_code != 200:
    print('Connection to agent down, at ' + agent_url)
    exit()
print('Connection to agent works!')

did = 'did:key:z6MkwZ9XcVLTNwkv8ELoxPu5q2dMkqLnE422ex69YMVX4hpr'
didkey = {
 "kty":"OKP",
 "kid":"z6MkwZ9XcVLTNwkv8ELoxPu5q2dMkqLnE422ex69YMVX4hpr",
 "crv":"Ed25519",
 "alg":"EdDSA",
 "x": "_hjLQG4OZMUagNFaKNvOkPTTzpKVWC2eKCdgN4QILM8",
 "d": "Rh4-MEIiWoC8clgA2mnT_CAjiSQ0OeO96BYI0mrcmYE"
 }

r = requests.post(agent_url + '/kms/import', json=didkey, verify=False)
print('KMS Import: {} {}'.format(r.status_code, r.text))
