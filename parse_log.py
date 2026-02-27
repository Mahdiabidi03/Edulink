import sys
with open('var/log/dev.log', 'r', encoding='utf-8', errors='replace') as f:
    lines = f.readlines()[-5000:]
current_error = []
errors = []
for line in lines:
    if 'request.CRITICAL' in line or 'Uncaught PHP Exception' in line:
        if current_error:
            errors.append("".join(current_error))
        current_error = [line]
    elif current_error:
        if line.startswith('['):
            errors.append("".join(current_error))
            current_error = []
        else:
            current_error.append(line)
if current_error:
    errors.append("".join(current_error))

with open('error.txt', 'w', encoding='utf-8') as out:
    if not errors:
        out.write("No CRITICAL errors found.\n")
    else:
        out.write("LATEST ERROR:\n")
        out.write(errors[-1][:2000] + "\n")
