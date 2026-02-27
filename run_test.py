import subprocess
try:
    result = subprocess.run(['php', 'test_context_manual.php'], capture_output=True, text=True)
    with open('manual_error.txt', 'w') as f:
        f.write(result.stdout)
        f.write(result.stderr)
except Exception as e:
    pass
