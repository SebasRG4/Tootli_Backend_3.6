import json

try:
    with open('/Users/giovannavilchis/.gemini/antigravity-ide/brain/011e1311-4b1a-451c-8093-33677b17f68d/.system_generated/logs/transcript.jsonl', 'r') as f:
        for line in f:
            data = json.loads(line)
            if data.get("type") == "PLANNER_RESPONSE" and "tool_calls" in data:
                for call in data["tool_calls"]:
                    if call.get("name") in ("replace_file_content", "multi_replace_file_content"):
                        args = call.get("arguments", {})
                        if not args:
                            args = json.loads(call.get("argumentsJson", "{}"))
                        
                        if "index.blade.php" in args.get("TargetFile", ""):
                            print("=== FOUND EDIT ===")
                            print(args.get("ReplacementContent") or args.get("ReplacementChunks"))
                            print("==================")
except Exception as e:
    print(e)
