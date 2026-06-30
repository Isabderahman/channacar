import type { H3Event } from 'h3'

type BackendNamespace = 'api' | 'storage'

export const buildBackendTarget = (
  event: H3Event,
  namespace: BackendNamespace,
  path: string,
  search = '',
) => {
  const runtimeConfig = useRuntimeConfig(event)
  const backendOrigin = runtimeConfig.backendOrigin.replace(/\/$/, '')
  const normalizedPath = path.replace(/^\/+/, '')
  const namespacedPath = normalizedPath ? `/${namespace}/${normalizedPath}` : `/${namespace}`

  return `${backendOrigin}${namespacedPath}${search}`
}
