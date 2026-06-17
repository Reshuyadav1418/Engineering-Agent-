import os
import re

replacements = {
    # Chart tooltips
    r"backgroundColor:\s*['\"]#0e1018['\"]": "backgroundColor: '#ffffff'",
    r"backgroundColor:\s*['\"]#0f0f1a['\"]": "backgroundColor: '#ffffff'",
    r"titleColor:\s*['\"]#e2e8f0['\"]": "titleColor: '#0f172a'",
    r"bodyColor:\s*['\"]#94a3b8['\"]": "bodyColor: '#475569'",
    r"grid:\s*\{\s*color:\s*['\"]rgba\(255,\s*255,\s*255,\s*0\.03\)['\"]": "grid: { color: 'rgba(0, 0, 0, 0.05)'",
    r"grid:\s*\{\s*color:\s*['\"]rgba\([^,]+,[^,]+,[^,]+,\s*0\.06\)['\"]": "grid: { color: 'rgba(0,0,0,0.05)'",
    
    # Specific colors
    r"color:\s*['\"]#e2e8f0['\"]": "color: '#0f172a'",
    r"color:\s*['\"]#64748b['\"]": "color: '#475569'",
    
    # More inline colors
    r"background:#0e1018;": "background:var(--bg-surface);",
    r"color:#e2e8f0;": "color:var(--text-primary);",
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
        print(f"Updated charts in {filepath}")

for root, dirs, files in os.walk('resources/views'):
    for file in files:
        if file.endswith('.blade.php'):
            process_file(os.path.join(root, file))

print("Chart replacement done.")
