import { getHeader, getRequestURL, getRouterParam, proxyRequest } from 'h3'

import { buildBackendTarget } from '../../../utils/backendTarget'

export default defineEventHandler((event) => {
  const path = getRouterParam(event, 'path') ?? ''
  const target = buildBackendTarget(event, 'api', path, getRequestURL(event).search)

  // h3's proxyRequest drops the incoming `Accept` header (it's in h3's
  // ignoredHeaders set), so Laravel stops treating the request as JSON. That
  // silently breaks error handling: a failed `$request->validate()` (e.g. wrong
  // login credentials) or an auth failure would render as a 302 redirect to the
  // web `/` welcome page (proxy fetch follows it → 200 HTML) or a 500, instead
  // of a 422/401 JSON body. Re-assert JSON so the backend keeps API semantics.
  const accept = getHeader(event, 'accept')
  return proxyRequest(event, target, {
    headers: {
      accept: accept && accept.includes('json') ? accept : 'application/json',
    },
  })
})
