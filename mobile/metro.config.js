const http = require("http");
const https = require("https");
const { getDefaultConfig } = require("expo/metro-config");

const config = getDefaultConfig(__dirname);

// The web page's Content-Security-Policy only allows requests to its own origin, so in development
// the dev server forwards API calls to the Laravel backend (same fallback as services/guardianApi.ts).
const apiUrl = (
  process.env.EXPO_PUBLIC_API_URL?.trim() || "http://localhost/LittleStep/backend/public"
).replace(/\/+$/, "");

config.server.enhanceMiddleware = (middleware) => (req, res, next) => {
  if (!/^\/(api\/|privacy(?:[/?]|$))/.test(req.url)) {
    return middleware(req, res, next);
  }

  const target = new URL(apiUrl + req.url);
  const client = target.protocol === "https:" ? https : http;
  const upstream = client.request(
    target,
    { method: req.method, headers: { ...req.headers, host: target.host } },
    (response) => {
      res.writeHead(response.statusCode, response.headers);
      response.pipe(res);
    },
  );

  upstream.on("error", () => {
    res.writeHead(502);
    res.end();
  });
  req.pipe(upstream);
};

module.exports = config;
