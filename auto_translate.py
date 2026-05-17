import re
import urllib.request
import urllib.parse
import json
import time
import os
import ssl

file_path = '/Users/giovannavilchis/Herd/back3.6/resources/lang/es/messages.php'

# Read the file
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Regex to find all key-value pairs
pattern = re.compile(r"('(?:\\'|[^'])*')\s*=>\s*('(?:\\'|[^'])*')")
matches = pattern.findall(content)

print(f"Found {len(matches)} keys to check/translate.")

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

def translate_chunk(texts):
    if not texts:
        return []
    
    separator = " |~| "
    combined_text = separator.join(texts)
    
    url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=es&dt=t&q=" + urllib.parse.quote(combined_text)
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    
    try:
        with urllib.request.urlopen(req, timeout=10, context=ctx) as response:
            result = json.loads(response.read().decode('utf-8'))
            translated_combined = ""
            for item in result[0]:
                if item[0]:
                    translated_combined += item[0]
            
            split_pattern = re.compile(r'\s*\|\s*~\s*\|\s*')
            translated_texts = split_pattern.split(translated_combined)
            
            if len(translated_texts) == len(texts):
                return translated_texts
            else:
                return None
    except Exception as e:
        print(f"Error during translation request: {e}")
        return None

def translate_individual(texts):
    translated = []
    for text in texts:
        url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=es&dt=t&q=" + urllib.parse.quote(text)
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        try:
            with urllib.request.urlopen(req, timeout=5, context=ctx) as response:
                result = json.loads(response.read().decode('utf-8'))
                t = "".join([item[0] for item in result[0] if item[0]])
                translated.append(t)
        except Exception:
            translated.append(text) 
        time.sleep(0.1)
    return translated

batch_size = 40
translated_dict = {}
keys_values = []
for k, v in matches:
    clean_v = v[1:-1].replace("\\'", "'")
    keys_values.append((k, v, clean_v))

total = len(keys_values)
print("Starting translation...")

for i in range(0, total, batch_size):
    chunk = keys_values[i:i+batch_size]
    texts = [c[2] for c in chunk]
    
    translated = translate_chunk(texts)
    if not translated:
        translated = translate_individual(texts)
    
    for j, (k, orig_v, clean_v) in enumerate(chunk):
        if j < len(translated):
            t = translated[j]
            t = t.replace("'", "\\'")
            if clean_v and clean_v[0].isupper() and t and t[0].islower():
                t = t[0].upper() + t[1:]
            translated_dict[k] = f"'{t}'"
        else:
            translated_dict[k] = orig_v
            
    if i % 400 == 0:
        print(f"Processed {i}/{total}...")
    time.sleep(0.2)

print("Translation done. Replacing in file...")

lines = content.split('\n')
new_lines = []
for line in lines:
    match = re.search(r"('(?:\\'|[^'])*')\s*=>\s*('(?:\\'|[^'])*')", line)
    if match:
        k = match.group(1)
        if k in translated_dict:
            new_line = line[:match.start(2)] + translated_dict[k] + line[match.end(2):]
            new_lines.append(new_line)
            continue
    new_lines.append(line)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write('\n'.join(new_lines))

print("All done!")
