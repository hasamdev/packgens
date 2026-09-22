# -*- coding: utf-8 -*-
"""Write the Packgens inline SVG icon set."""
import io
import os
import sys

OUT = sys.argv[1]
os.makedirs(OUT, exist_ok=True)

L = ('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
     'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">%s</svg>')
F = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" focusable="false">%s</svg>'

line = {
    'search': '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
    'phone': '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
    'truck': '<path d="M1 3h13v13H1z"/><path d="M14 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="17.5" cy="18.5" r="2.5"/>',
    'heart': '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1L12 21.2l7.7-7.8 1.1-1a5.5 5.5 0 0 0 0-7.8z"/>',
    'user': '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'cart': '<circle cx="9" cy="21" r="1.6"/><circle cx="19" cy="21" r="1.6"/><path d="M1 1h3.2l2.7 12.4a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L23 6H6"/>',
    'chevron-down': '<path d="M6 9l6 6 6-6"/>',
    'chevron-up': '<path d="M18 15l-6-6-6 6"/>',
    'chevron-left': '<path d="M15 18l-6-6 6-6"/>',
    'chevron-right': '<path d="M9 18l6-6-6-6"/>',
    'chevrons-right': '<path d="M7 17l5-5-5-5"/><path d="M14 17l5-5-5-5"/>',
    'arrow-right': '<path d="M4 12h16"/><path d="M14 6l6 6-6 6"/>',
    'arrow-up-right': '<path d="M7 17L17 7"/><path d="M8 7h9v9"/>',
    'arrow-left': '<path d="M20 12H4"/><path d="M10 6l-6 6 6 6"/>',
    'menu': '<path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h18"/>',
    'close': '<path d="M18 6L6 18"/><path d="M6 6l12 12"/>',
    'plus': '<path d="M12 5v14"/><path d="M5 12h14"/>',
    'minus': '<path d="M5 12h14"/>',
    'check': '<path d="M20 6L9 17l-5-5"/>',
    'mail': '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/>',
    'map-pin': '<path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
    'clock': '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
    'message-circle': '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.9 8.9 0 0 1-4-.9L3 21l1.9-4.9A8.4 8.4 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5z"/>',
    'headphones': '<path d="M3 17v-5a9 9 0 0 1 18 0v5"/><path d="M21 18a2 2 0 0 1-2 2h-1v-6h1a2 2 0 0 1 2 2z"/><path d="M3 18a2 2 0 0 0 2 2h1v-6H5a2 2 0 0 0-2 2z"/>',
    'shield-check': '<path d="M12 2l8 3.5v6c0 5-3.4 9.4-8 10.5-4.6-1.1-8-5.5-8-10.5v-6z"/><path d="M9 12l2 2 4-4"/>',
    'zap': '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
    'package': '<path d="M21 16V8l-9-5-9 5v8l9 5z"/><path d="M3.3 7.3L12 12l8.7-4.7"/><path d="M12 12v9"/>',
    'leaf': '<path d="M4 20s0-9 8-12c4-1.5 8-2 8-2s0 6-2 10-6 6-10 6a4 4 0 0 1-4-2z"/><path d="M9 15c2-3 5-5 8-6"/>',
    'award': '<circle cx="12" cy="9" r="6"/><path d="M8.2 13.8L7 22l5-3 5 3-1.2-8.2"/>',
    'thumbs-up': '<path d="M7 21H4a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1h3"/><path d="M7 11l4-8a2.5 2.5 0 0 1 2.5 2.5V9h5A2 2 0 0 1 20.5 11l-1.6 7A2 2 0 0 1 17 20H7z"/>',
    'ticket-percent': '<path d="M2 9V6a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v3a3 3 0 0 0 0 6v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-3a3 3 0 0 0 0-6z"/><path d="M9.5 9.5h.01"/><path d="M14.5 14.5h.01"/><path d="M15 9l-6 6"/>',
    'calendar': '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M3 10h18"/>',
    'link': '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7L11.8 5"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7L12.2 19"/>',
    'expand': '<path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/>',
    'paperclip': '<path d="M21.4 11.1l-9.2 9.2a5.5 5.5 0 0 1-7.8-7.8l9.2-9.2a3.7 3.7 0 0 1 5.2 5.2l-9.2 9.2a1.8 1.8 0 0 1-2.6-2.6l8.5-8.5"/>',
    'filter': '<path d="M3 5h18l-7 8v6l-4 2v-8z"/>',
    'lock': '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
    'globe': '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18z"/>',
    'printer': '<path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 15h12v6H6z"/>',
    'pencil-ruler': '<path d="M4 20l4-1 10-10-3-3L5 16z"/><path d="M14 6l4 4"/><path d="M16 3l5 5"/>',
    'file-text': '<path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/><path d="M14 2v5h5"/><path d="M9 13h6"/><path d="M9 17h4"/>',
    'sparkles': '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/><path d="M18.5 15.5l.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8z"/>',
    'refresh': '<path d="M21 12a9 9 0 1 1-2.6-6.4"/><path d="M21 4v5h-5"/>',
    'credit-card': '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    'quote': '<path d="M9 11H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v8a4 4 0 0 1-4 4"/><path d="M21 11h-4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v8a4 4 0 0 1-4 4"/>',
    'calculator': '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8"/><path d="M8 11h.01"/><path d="M12 11h.01"/><path d="M16 11h.01"/><path d="M8 15h.01"/><path d="M12 15h.01"/><path d="M16 15h.01"/><path d="M8 19h8"/>',
    'help-circle': '<circle cx="12" cy="12" r="9"/><path d="M9.2 9.2a3 3 0 0 1 5.8 1c0 2-3 2.5-3 4"/><path d="M12 17h.01"/>',
    'grid': '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    'layers': '<path d="M12 2L2 7l10 5 10-5z"/><path d="M2 12l10 5 10-5"/><path d="M2 17l10 5 10-5"/>',
}

fill = {
    'star': '<path d="M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.4l-5.8 3.1 1.1-6.5L2.6 9.4l6.5-.9z"/>',
    'check-circle': '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14.4l-4-4L8.4 11l2.6 2.6L15.6 9 17 10.4z"/>',
    'x-circle': '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm3.5 12.1L14.1 15.5 12 13.4l-2.1 2.1-1.4-1.4L10.6 12 8.5 9.9l1.4-1.4L12 10.6l2.1-2.1 1.4 1.4L13.4 12z"/>',
    'facebook': '<path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z"/>',
    'instagram': '<path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.2 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.2 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.2-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.2-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4 1.3-.1 1.7-.1 4.8-.1zm0 3.1A6.7 6.7 0 1 0 18.7 12 6.7 6.7 0 0 0 12 5.3zm0 11A4.3 4.3 0 1 1 16.3 12 4.3 4.3 0 0 1 12 16.3zm6.9-11.2a1.6 1.6 0 1 1-1.6-1.6 1.6 1.6 0 0 1 1.6 1.6z"/>',
    'linkedin': '<path d="M20.4 2H3.6A1.6 1.6 0 0 0 2 3.6v16.8A1.6 1.6 0 0 0 3.6 22h16.8a1.6 1.6 0 0 0 1.6-1.6V3.6A1.6 1.6 0 0 0 20.4 2zM8.1 18.7H5.2V9.6h2.9zM6.6 8.3a1.7 1.7 0 1 1 1.7-1.7 1.7 1.7 0 0 1-1.7 1.7zm12.1 10.4h-2.9v-4.4c0-1.1 0-2.4-1.5-2.4s-1.7 1.1-1.7 2.3v4.5H9.7V9.6h2.8v1.2h.1a3.1 3.1 0 0 1 2.8-1.5c3 0 3.5 2 3.5 4.5z"/>',
    'youtube': '<path d="M22.5 7s-.2-1.5-.9-2.1c-.8-.9-1.7-.9-2.1-1C16.6 3.6 12 3.6 12 3.6s-4.6 0-7.5.2c-.4.1-1.3.1-2.1 1C1.7 5.5 1.5 7 1.5 7S1.3 8.7 1.3 10.5v1.7c0 1.8.2 3.5.2 3.5s.2 1.5.9 2.1c.8.9 1.9.8 2.4.9 1.7.2 7.2.2 7.2.2s4.6 0 7.5-.2c.4-.1 1.3-.1 2.1-1 .7-.6.9-2.1.9-2.1s.2-1.7.2-3.5v-1.7c0-1.8-.2-3.4-.2-3.4zM9.9 14.6V8.4l6 3.1z"/>',
    'x': '<path d="M17.5 3h3.2l-7 8 8.3 10h-6.5l-5.1-6.2L4.6 21H1.4l7.5-8.6L1 3h6.6l4.6 5.7zm-1.1 16.2h1.8L7.7 4.7H5.8z"/>',
    'whatsapp': '<path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A9.9 9.9 0 1 0 12 2zm5.8 14.1c-.2.7-1.4 1.3-2 1.4a4 4 0 0 1-1.8-.1 15.9 15.9 0 0 1-1.7-.6 12.8 12.8 0 0 1-4.9-4.3 5.6 5.6 0 0 1-1.2-3 3.2 3.2 0 0 1 1-2.4 1.1 1.1 0 0 1 .8-.4h.6c.2 0 .4 0 .6.5s.8 2 .9 2.1a.5.5 0 0 1 0 .5 2 2 0 0 1-.3.5l-.4.5a.5.5 0 0 0-.1.6 9.3 9.3 0 0 0 1.7 2.1 8.4 8.4 0 0 0 2.4 1.5c.3.2.5.1.7-.1s.8-.9 1-1.2.4-.3.7-.2l2 1c.3.1.5.2.6.3a2.1 2.1 0 0 1-.1 1.3z"/>',
    'trustpilot': '<path d="M12 1.6l3.2 6.6 7.3 1-5.3 5.1 1.3 7.2-6.5-3.4-6.5 3.4 1.3-7.2L1.5 9.2l7.3-1z"/>',
}

for name, body in line.items():
    io.open(os.path.join(OUT, name + '.svg'), 'w', encoding='utf-8', newline='\n').write(L % body)
for name, body in fill.items():
    io.open(os.path.join(OUT, name + '.svg'), 'w', encoding='utf-8', newline='\n').write(F % body)

print(len(line) + len(fill), 'icons written to', OUT)
