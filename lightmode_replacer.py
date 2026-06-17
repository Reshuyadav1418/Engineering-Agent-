import os
import re

replacements = {
    # Backgrounds
    r'(?i)background:\s*#0a0a0f;': 'background:var(--bg-base);',
    r'(?i)background:\s*#07080f;': 'background:var(--bg-base);',
    r'(?i)background:\s*#0e1018;': 'background:var(--bg-surface);',
    r'(?i)background:\s*#0f0f1a;': 'background:var(--bg-surface);',
    r'(?i)background:\s*#13161f;': 'background:var(--bg-elevated);',
    r'(?i)background:\s*#1e1e2d;': 'background:var(--bg-elevated);',
    r'(?i)background:\s*#131320;': 'background:var(--bg-elevated);',
    
    # Colors
    r'(?i)color:\s*#f1f5f9;': 'color:var(--text-primary);',
    r'(?i)color:\s*#e2e8f0;': 'color:var(--text-primary);',
    r'(?i)color:\s*#e0e7ff;': 'color:var(--text-primary);',
    r'(?i)color:\s*#94a3b8;': 'color:var(--text-secondary);',
    r'(?i)color:\s*#64748b;': 'color:var(--text-muted);',
    r'(?i)color:\s*#475569;': 'color:var(--text-muted);',
    
    # Fills & Strokes (SVG)
    r'(?i)stroke="#334155"': 'stroke="currentColor"',
    r'(?i)stroke="#475569"': 'stroke="currentColor"',
    
    # Specific rgba overrides for light mode borders
    r'(?i)rgba\(255,255,255,0\.02\)': 'rgba(0,0,0,0.02)',
    r'(?i)rgba\(255,255,255,0\.03\)': 'rgba(0,0,0,0.03)',
    r'(?i)rgba\(255,255,255,0\.04\)': 'rgba(0,0,0,0.04)',
    r'(?i)rgba\(255,255,255,0\.05\)': 'rgba(0,0,0,0.05)',
    r'(?i)rgba\(255,255,255,0\.1\)': 'rgba(0,0,0,0.08)',
    
    # Remove HTML dark class
    r'class="dark"': '',
}

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        
    original = content
    for pattern, replacement in replacements.items():
        content = re.sub(pattern, replacement, content)
        
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, dirs, files in os.walk('resources/views'):
    for file in files:
        if file.endswith('.blade.php'):
            process_file(os.path.join(root, file))

print("Done replacing.")
