/**
 * Letters Max: API Service for GoDaddy Hosting
 * This replaces the direct Supabase client calls.
 */

const API_BASE = 'https://lettersmax.acamix.com/api';

export const callApi = async (endpoint: string, options: RequestInit = {}) => {
  const url = `${API_BASE}/${endpoint}`;
  
  // Default headers for JSON
  const headers = {
    'Content-Type': 'application/json',
    ...(options.headers || {}),
  };

  try {
    const response = await fetch(url, { ...options, headers });
    if (!response.ok) {
      throw new Error(`API Error: ${response.statusText}`);
    }
    return await response.json();
  } catch (error) {
    console.error(`Fetch error for ${endpoint}:`, error);
    return { success: false, error: (error as Error).message };
  }
};
