import os
import re
import sys

def get_file_content(file_path):
    try:
        with open(file_path, 'r') as f:
            return f.read()
    except FileNotFoundError:
        print(f"Warning: File not found: {file_path}")
        return None

def get_language(file_path):
    ext = os.path.splitext(file_path)[1].lower()
    if ext == '.php': return 'php'
    if ext == '.json': return 'json'
    if ext == '.js': return 'javascript'
    if ext == '.yml' or ext == '.yaml': return 'yaml'
    if ext == '.xml': return 'xml'
    if ext == '.md': return 'markdown'
    if ext == '.sh': return 'bash'
    return ''

def process_file(filepath):
    with open(filepath, 'r') as f:
        original_content = f.read()

    content = original_content

    # Regex to find the gitbook embed tag
    # Pattern handles the example: {% @github-files/github-code-block url="..." ... %}
    # Using non-greedy match for the content inside the tag
    pattern_embed = r'\{%\s*@github-files/github-code-block\s+url="(https://github\.com/[^"]+)"[^%]*%\}'
    
    def replace_embed(match):
        url = match.group(1)
        # Extract file path from URL. Assumes standard GitHub blob URL structure:
        # https://github.com/user/repo/blob/branch/path/to/file
        
        parts = url.split('/blob/')
        if len(parts) < 2:
            print(f"Warning: Could not parse URL: {url}")
            return match.group(0)
        
        rest = parts[1]
        # Split on first slash to separate branch from path
        path_parts = rest.split('/', 1)
        if len(path_parts) < 2:
             print(f"Warning: Could not extract path from: {rest}")
             return match.group(0)
             
        relative_path = path_parts[1]
        
        # In the workflow, we are in the root of the repo.
        # The file should be at relative_path.
        
        file_content = get_file_content(relative_path)
        if file_content is None:
            return match.group(0)
            
        language = get_language(relative_path)
        
        return f"```{language}\n{file_content}\n```"

    content = re.sub(pattern_embed, replace_embed, content)

    # Regex to find GitBook hints
    # Pattern: {% hint style="style" %} ... {% endhint %}
    # Uses dotall matching to capture multiline content
    pattern_hint = r'\{%\s*hint\s+style="([^"]+)"\s*%\}(.*?)\{%\s*endhint\s*%\}'
    
    hint_map = {
        'info': 'NOTE',
        'success': 'TIP',
        'warning': 'WARNING',
        'danger': 'CAUTION'
    }
    
    def replace_hint(match):
        style = match.group(1)
        hint_content = match.group(2).strip()
        
        github_type = hint_map.get(style, 'NOTE') # Default to NOTE if style is unknown
        
        # Format the content: add "> " to each line
        formatted_lines = []
        for line in hint_content.splitlines():
            formatted_lines.append(f"> {line}")
            
        formatted_content = "\n".join(formatted_lines)
        
        return f"> [!{github_type}]\n{formatted_content}"

    # Use re.DOTALL to allow dot to match newlines in hint content
    content = re.sub(pattern_hint, replace_hint, content, flags=re.DOTALL)
    
    if content != original_content:
        print(f"Updated {filepath}")
        with open(filepath, 'w') as f:
            f.write(content)

def main():
    docs_dir = 'docs'
    if not os.path.exists(docs_dir):
        print(f"Directory not found: {docs_dir}")
        sys.exit(1)

    for root, dirs, files in os.walk(docs_dir):
        for file in files:
            if file.endswith('.md'):
                process_file(os.path.join(root, file))

if __name__ == "__main__":
    main()

