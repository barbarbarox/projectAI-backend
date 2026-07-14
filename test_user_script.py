import sys
import os

try:
    from openai import OpenAI
    client = OpenAI(
      base_url = "https://integrate.api.nvidia.com/v1",
      api_key = "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"
    )
    completion = client.chat.completions.create(
      model="deepseek-ai/deepseek-v4-pro",
      messages=[{"content":"halo apa kabar?","role":"user"}],
      temperature=1,
      top_p=0.95,
      max_tokens=100,
      stream=True
    )
    for chunk in completion:
      if not getattr(chunk, "choices", None):
        continue
      if chunk.choices and chunk.choices[0].delta.content is not None:
        print(chunk.choices[0].delta.content, end="")
        sys.stdout.flush()
    print("\nDONE")
except Exception as e:
    print("Error:", e)
