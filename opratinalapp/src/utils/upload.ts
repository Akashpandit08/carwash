export async function parseUploadResponse(response: Response) {
  const text = await response.text();
  const data = text ? JSON.parse(text) : {};

  if (!response.ok) {
    const error: any = new Error(data?.message || 'Upload failed');
    error.response = { status: response.status, data };
    throw error;
  }

  return data;
}
