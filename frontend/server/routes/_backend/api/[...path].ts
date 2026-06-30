import { getRequestURL, getRouterParam, proxyRequest } from 'h3'

import { buildBackendTarget } from '../../../utils/backendTarget'

export default defineEventHandler((event) => {
  const path = getRouterParam(event, 'path') ?? ''
  const target = buildBackendTarget(event, 'api', path, getRequestURL(event).search)

  // NOTE: do not stream the request. Streaming forwards the body with chunked
  // transfer-encoding, which PHP's built-in dev server (`php artisan serve`)
  // cannot parse for multipart uploads — it resets the connection (proxy 502).
  // Buffering sends a Content-Length, which the dev server handles correctly.
  return proxyRequest(event, target)
})
