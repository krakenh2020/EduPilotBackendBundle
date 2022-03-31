import requests
from requests.packages.urllib3.exceptions import InsecureRequestWarning
requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

// keys generated using https://github.com/transmute-industries/did-key.js
// via https://www.npmjs.com/package/@transmute/did-key-ed25519
// using https://gist.github.com/stefan2904/f1ac36cebe946a475bcbbb8c3e8960c9


agent_url = 'http://localhost:8082'

print('Check connection uni ...')
r = requests.get(agent_url + '/connections', verify=False)
if r.status_code != 200:
    print('Connection to uni agent down :(')
    exit()
print('Connection to uni agent works!')

did = 'did:key:z6MkwZ9XcVLTNwkv8ELoxPu5q2dMkqLnE422ex69YMVX4hpr'
didkey = {
 "kty":"OKP",
 "kid":"z6MkwZ9XcVLTNwkv8ELoxPu5q2dMkqLnE422ex69YMVX4hpr",
 "crv":"Ed25519",
 "alg":"EdDSA",
 "x": "_hjLQG4OZMUagNFaKNvOkPTTzpKVWC2eKCdgN4QILM8",
 "d": "Rh4-MEIiWoC8clgA2mnT_CAjiSQ0OeO96BYI0mrcmYE"
 }

print('Importing {}'.format(did))

r = requests.post(agent_url + '/kms/import', json=didkey, verify=False)
print('KMS Import: {} {}'.format(r.status_code, r.text))





agent_url = 'http://localhost:8092'

print('Check connection student ...')
r = requests.get(agent_url + '/connections', verify=False)
if r.status_code != 200:
    print('Connection to student agent down :(')
    exit()
print('Connection to student agent works!')

did = 'did:key:z6Mkk7yqnGF3YwTrLpqrW6PGsKci7dNqh1CjnvMbzrMerSeL'
didkey = {
 "kty":"OKP",
 "kid":"z6Mkk7yqnGF3YwTrLpqrW6PGsKci7dNqh1CjnvMbzrMerSeL",
 "crv":"Ed25519",
 "alg":"EdDSA",
 "x": "VDXDwuGKVq91zxU6q7__jLDUq8_C5cuxECgd-1feFTE",
 "d": "T2azVap7CYD_kB8ilbnFYqwwYb5N-GcD6yjGEvquZXg"
 }

print('Importing {}'.format(did))

r = requests.post(agent_url + '/kms/import', json=didkey, verify=False)
print('KMS Import: {} {}'.format(r.status_code, r.text))
