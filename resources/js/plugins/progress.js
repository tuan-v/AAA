import NProgress from 'nprogress'
import 'nprogress/nprogress.css'
import { router } from '@inertiajs/vue3'

export function setupProgress() {
    NProgress.configure({ showSpinner: false, trickleSpeed: 200, minimum: 0.08 })

    let timeout = null
    let activeRequests = 0

    router.on('start', () => {
        activeRequests++
        clearTimeout(timeout)
        timeout = setTimeout(() => {
            if (activeRequests > 0) NProgress.start()
        }, 100)
    })

    router.on('progress', (event) => {
        if (event?.detail?.progress?.percentage) {
            NProgress.set(event.detail.progress.percentage / 100)
        }
    })

    router.on('finish', (event) => {
        activeRequests = Math.max(0, activeRequests - 1)
        if (activeRequests > 0) return
        clearTimeout(timeout)
        NProgress.done()
        if (!event?.detail?.visit?.completed) NProgress.remove()
    })

    router.on('exception', () => {
        activeRequests = Math.max(0, activeRequests - 1)
        clearTimeout(timeout)
        NProgress.done()
    })
}