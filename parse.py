import re
with open('var/log/dev.log', 'r', encoding='utf-8', errors='replace') as f:
    lines = f.readlines()[-5000:]
out = open('out.txt', 'w', encoding='utf-8')
for line in lines:
    if 'Uncaught PHP Exception' in line and 'api/chat' in line:
        out.write(line + '\n')
out.close()
