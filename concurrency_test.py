import requests
import concurrent.futures
import time

import sys

URL = "http://localhost/api/lottery/draw"
if len(sys.argv) > 1:
    URL = sys.argv[1]
    
CONCURRENCY = 20
TOTAL_REQUESTS = 20

def draw_lottery(i):
    try:
        start = time.time()
        # openid is hardcoded in PHP logic as 'local_test_openid'
        # but we send 'test' to satisfy required param check
        resp = requests.post(URL, data={'openid': 'test'}, timeout=10)
        elapsed = time.time() - start
        return i, resp.status_code, resp.text, elapsed
    except Exception as e:
        return i, -1, str(e), 0

def main():
    print(f"Starting {TOTAL_REQUESTS} requests with concurrency {CONCURRENCY}...")
    start_total = time.time()
    
    with concurrent.futures.ThreadPoolExecutor(max_workers=CONCURRENCY) as executor:
        futures = [executor.submit(draw_lottery, i) for i in range(TOTAL_REQUESTS)]
        
        success_count = 0
        fail_count = 0
        lock_fail_count = 0
        
        for future in concurrent.futures.as_completed(futures):
            i, status, text, elapsed = future.result()
            # print(f"Req {i}: Status {status} Time {elapsed:.2f}s")
            
            if status == 200:
                if '"code":1' in text:
                    success_count += 1
                elif '参与人数过多' in text or '系统繁忙' in text:
                    lock_fail_count += 1
                    print(f"Req {i}: Lock Busy ({elapsed:.2f}s)")
                else:
                    fail_count += 1
                    # Extract message from json if possible
                    print(f"Req {i} Failed: {text[:100]}")
            else:
                fail_count += 1
                print(f"Req {i} Error: {status} {text[:100]}")

    print(f"\nTotal Time: {time.time() - start_total:.2f}s")
    print(f"Success (Won): {success_count}")
    print(f"Lock Busy (Try Later): {lock_fail_count}")
    print(f"Failed (Other): {fail_count}")

if __name__ == "__main__":
    main()
