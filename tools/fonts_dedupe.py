# -*- coding: utf-8 -*-
"""Collapse the downloaded Google variable fonts to one file per family+subset."""
import hashlib
import io
import os
import re
import sys

THEME = sys.argv[1]
FONT_DIR = os.path.join(THEME, 'assets', 'fonts')
CSS = os.path.join(THEME, 'assets', 'css', 'fonts.css')

# Existing rules give us the unicode-range per subset.
src = io.open(CSS, encoding='utf-8').read()
ranges = {}
for block in re.findall(r'@font-face\s*\{(.*?)\}', src, re.S):
    fam = re.search(r"font-family:\s*'([^']+)'", block).group(1)
    url = re.search(r"url\('\.\./fonts/([^']+)'\)", block).group(1)
    subset = 'latin-ext' if 'latin-ext' in url else 'latin'
    ur = re.search(r'unicode-range:\s*([^;]+);', block)
    ranges[(fam, subset)] = ur.group(1).strip() if ur else ''

# One canonical file per (family, subset); delete the byte-identical copies.
canonical = {}
by_hash = {}
for name in sorted(os.listdir(FONT_DIR)):
    if not name.endswith('.woff2'):
        continue
    path = os.path.join(FONT_DIR, name)
    digest = hashlib.md5(open(path, 'rb').read()).hexdigest()
    if digest in by_hash:
        os.remove(path)
        continue
    by_hash[digest] = name

for name in by_hash.values():
    subset = 'latin-ext' if 'latin-ext' in name else 'latin'
    family = 'Inter' if name.startswith('inter') else 'Plus Jakarta Sans'
    # Drop the weight from the filename now that one file covers every weight.
    slug = ('inter' if family == 'Inter' else 'plus-jakarta-sans') + '-variable-' + subset + '.woff2'
    os.rename(os.path.join(FONT_DIR, name), os.path.join(FONT_DIR, slug))
    canonical[(family, subset)] = slug

WEIGHTS = {'Inter': '100 900', 'Plus Jakarta Sans': '200 800'}

out = [
    '/* =========================================================================',
    '   Packgens - self-hosted variable webfonts (latin + latin-ext).',
    '   Inter and Plus Jakarta Sans, SIL Open Font License 1.1.',
    '   One variable file per family covers every weight the theme uses.',
    '   ========================================================================= */',
    '',
]

for (family, subset), slug in sorted(canonical.items()):
    out.append('@font-face {')
    out.append("\tfont-family: '%s';" % family)
    out.append('\tfont-style: normal;')
    out.append('\tfont-weight: %s;' % WEIGHTS[family])
    out.append('\tfont-display: swap;')
    out.append("\tsrc: url('../fonts/%s') format('woff2-variations');" % slug)
    if ranges.get((family, subset)):
        out.append('\tunicode-range: %s;' % ranges[(family, subset)])
    out.append('}')
    out.append('')

io.open(CSS, 'w', encoding='utf-8', newline='\n').write('\n'.join(out))
print('kept %d files:' % len(canonical), sorted(canonical.values()))
