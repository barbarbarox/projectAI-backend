from openai import OpenAI

client = OpenAI(
  base_url = "https://integrate.api.nvidia.com/v1",
  api_key = "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"
)

try:
    completion = client.chat.completions.create(
      model="deepseek-ai/deepseek-v4-pro",
      messages=[{"content":"test, apa kabr","role":"user"}],
      temperature=1,
      top_p=0.95,
      max_tokens=100,
      stream=False
    )
    print(completion.choices[0].message.content)
except Exception as e:
    print("Error:", e)
