const URL = 'https://api.open-meteo.com/v1/forecast'
  + '?latitude=41.15&longitude=-8.61'
  + '&current=temperature_2m,apparent_temperature,weather_code,wind_speed_10m,is_day,relative_humidity_2m'
  + '&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,sunrise,sunset'
  + '&timezone=Europe%2FLisbon&forecast_days=5';

export async function fetchWeather() {
  const res = await fetch(URL, {
    headers: { 'User-Agent': 'CesaeKioskBot/1.0' },
    signal: AbortSignal.timeout(5000),
  });
  if (!res.ok) throw new Error(`open-meteo HTTP ${res.status}`);
  const j = await res.json();

  const current = {
    tempC: round(j.current?.temperature_2m),
    feelsLikeC: round(j.current?.apparent_temperature),
    weatherCode: j.current?.weather_code ?? null,
    windKmh: round(j.current?.wind_speed_10m),
    humidity: round(j.current?.relative_humidity_2m),
    isDay: j.current?.is_day === 1,
    time: j.current?.time ?? null,
  };

  const daily = (j.daily?.time || []).map((date, i) => ({
    date,
    code: j.daily.weather_code?.[i] ?? null,
    maxC: round(j.daily.temperature_2m_max?.[i]),
    minC: round(j.daily.temperature_2m_min?.[i]),
    precipProb: j.daily.precipitation_probability_max?.[i] ?? null,
    sunrise: j.daily.sunrise?.[i] ?? null,
    sunset: j.daily.sunset?.[i] ?? null,
  }));

  return { current, daily, location: 'Porto' };
}

function round(n) {
  return typeof n === 'number' ? Math.round(n) : null;
}
