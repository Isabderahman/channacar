import { defineSitemapEventHandler } from '#imports'
import type { SitemapUrlInput } from '#sitemap/types'

interface CarLike {
  slug?: string | null
  updated_at?: string | null
}

interface PaginatedCars {
  data: CarLike[]
  current_page: number
  last_page: number
}

// Feeds the dynamic /cars/[slug] detail pages into the generated sitemap.
// Runs server-side and talks to the Laravel backend directly (not via the
// browser proxy). @nuxtjs/sitemap applies locale prefixes/alternates itself.
export default defineSitemapEventHandler(async (event) => {
  const runtimeConfig = useRuntimeConfig(event)
  const backendOrigin = runtimeConfig.backendOrigin.replace(/\/$/, '')

  const urls: SitemapUrlInput[] = []

  try {
    let page = 1
    let lastPage = 1

    do {
      const response = await $fetch<PaginatedCars>(`${backendOrigin}/api/public/cars`, {
        query: { page, per_page: 100 },
      })

      for (const car of response.data ?? []) {
        if (!car.slug) continue
        urls.push({
          loc: `/cars/${car.slug}`,
          lastmod: car.updated_at ?? undefined,
          changefreq: 'weekly',
          priority: 0.8,
        })
      }

      lastPage = response.last_page ?? 1
      page += 1
    } while (page <= lastPage)
  } catch {
    // If the backend is unreachable at generation time, fall back to the
    // static routes the module already discovered rather than failing the build.
    return []
  }

  return urls
})
