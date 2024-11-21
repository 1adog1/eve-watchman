import time
import json
import requests


from datetime import datetime

class TimerTerminus:

    def __init__(self, data, platform, url, token):

        self.posterFunctions = {
            "RC2": self.postToRC2
        }

        self.data = data
        self.platform = platform
        self.poster = self.posterFunctions[self.platform]
        self.url = url
        self.token = token

    def postToRC2(self):

        postRequest = requests.post(self.url, data=json.dumps(self.data), headers={"Content-Type":"application/json", "X-Bot-Auth":self.token})
        return postRequest

    def post(self, retries):

        for tries in range(retries + 1):

            postRequest = self.poster()

            if postRequest.status_code in [200, 204]:

                return True

            elif tries == retries:

                print("Failed to Post a Timer to {platform}!".format(platform=self.platform))
                break

            else:

                print("Unknown Error Posting a Timer to {platform} - Trying Again in 5 Seconds.".format(platform=self.platform))
                time.sleep(5)

        return False
