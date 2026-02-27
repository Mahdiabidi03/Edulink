with open('error_output.txt', 'r', encoding='utf-16le') as f:
    text = f.read()
lines = text.split('\n')
with open('error_clean.txt', 'w', encoding='utf-8') as out:
    for line in lines:
        if 'Fatal error' in line or 'Stack trace' in line or '#' in line:
            out.write(line.strip() + '\n')
