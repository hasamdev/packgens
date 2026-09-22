# -*- coding: utf-8 -*-
"""Download the latin subsets of the theme fonts and emit a self-hosted fonts.css."""
import io
import os
import re
import sys
import urllib.request

THEME = sys.argv[1]
FONT_DIR = os.path.join(THEME, 'assets', 'fonts')
CSS_OUT = os.path.join(THEME, 'assets', 'css', 'fonts.css')
os.makedirs(FONT_DIR, exist_ok=True)

UA = ('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
      '(KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36')
URL = ('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700'
       '&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap')


def get(url, binary=False):
    req = urllib.request.Request(url, headers={'User-Agent': UA})
    with urllib.request.urlopen(req, timeout=40) as r:
        data = r.read()
    return data if binary else data.decode('utf-8')


css = get(URL)

# Split on the subset comments Google emits before each @font-face.
blocks = re.findall(r"/\*\s*([a-z\-]+)\s*\*/\s*(@font-face\s*\{.*?\})", css, re.S)
KEEP = {'latin', 'latin-ext'}

out = [
    '/* =========================================================================',
    '   Packgens - self-hosted webfonts (latin + latin-ext subsets).',
    '   Regenerate with tools/fonts.py if the weight set changes.',
    '   Inter and Plus Jakarta Sans are licensed under the SIL Open Font License.',
    '   ========================================================================= */',
    '',
]
seen = set()
count = 0

for subset, block in blocks:
    if subset not in KEEP:
        continue

    family = re.search(r"font-family:\s*'([^']+)'", block).group(1)
    weight = re.search(r'font-weight:\s*(\d+)', block).group(1)
    style = re.search(r'font-style:\s*(\w+)', block).group(1)
    url = re.search(r'url\((https://[^)]+)\)', block).group(1)
    urange = re.search(r'unicode-range:\s*([^;]+);', block)

    slug = family.lower().replace(' ', '-') + '-' + weight + '-' + subset + '.woff2'
    path = os.path.join(FONT_DIR, slug)

    if slug not in seen:
        with open(path, 'wb') as fh:
            fh.write(get(url, binary=True))
        seen.add(slug)
        count += 1

    out.append('@font-face {')
    out.append("\tfont-family: '%s';" % family)
    out.append('\tfont-style: %s;' % style)
    out.append('\tfont-weight: %s;' % weight)
    out.append('\tfont-display: swap;')
    out.append("\tsrc: url('../fonts/%s') format('woff2');" % slug)
    if urange:
        out.append('\tunicode-range: %s;' % urange.group(1).strip())
    out.append('}')
    out.append('')

io.open(CSS_OUT, 'w', encoding='utf-8', newline='\n').write('\n'.join(out))
print('%d font files downloaded, %d @font-face rules written' % (count, len(seen)))
