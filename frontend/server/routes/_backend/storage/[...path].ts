import { getRequestURL, getRouterParam, proxyRequest } from 'h3'

import { buildBackendTarget } from '../../../utils/backendTarget'

export default defineEventHandler((event) => {
  const path = getRouterParam(event, 'path') ?? ''
  const target = buildBackendTarget(event, 'storage', path, getRequestURL(event).search)

  return proxyRequest(event, target, {
    streamRequest: true,
  })
})
