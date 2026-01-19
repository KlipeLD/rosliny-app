import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\PlantEntryController::index
 * @see app/Http/Controllers/PlantEntryController.php:11
 * @route '/plants/{plant}/entries'
 */
export const index = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/plants/{plant}/entries',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlantEntryController::index
 * @see app/Http/Controllers/PlantEntryController.php:11
 * @route '/plants/{plant}/entries'
 */
index.url = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plant: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { plant: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    plant: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        plant: typeof args.plant === 'object'
                ? args.plant.id
                : args.plant,
                }

    return index.definition.url
            .replace('{plant}', parsedArgs.plant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantEntryController::index
 * @see app/Http/Controllers/PlantEntryController.php:11
 * @route '/plants/{plant}/entries'
 */
index.get = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PlantEntryController::index
 * @see app/Http/Controllers/PlantEntryController.php:11
 * @route '/plants/{plant}/entries'
 */
index.head = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PlantEntryController::index
 * @see app/Http/Controllers/PlantEntryController.php:11
 * @route '/plants/{plant}/entries'
 */
    const indexForm = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PlantEntryController::index
 * @see app/Http/Controllers/PlantEntryController.php:11
 * @route '/plants/{plant}/entries'
 */
        indexForm.get = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PlantEntryController::index
 * @see app/Http/Controllers/PlantEntryController.php:11
 * @route '/plants/{plant}/entries'
 */
        indexForm.head = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\PlantEntryController::fetch
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
export const fetch = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: fetch.url(args, options),
    method: 'post',
})

fetch.definition = {
    methods: ["post"],
    url: '/plants/{plant}/entries/fetch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PlantEntryController::fetch
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
fetch.url = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plant: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    plant: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        plant: args.plant,
                }

    return fetch.definition.url
            .replace('{plant}', parsedArgs.plant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantEntryController::fetch
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
fetch.post = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: fetch.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PlantEntryController::fetch
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
    const fetchForm = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: fetch.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PlantEntryController::fetch
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
        fetchForm.post = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: fetch.url(args, options),
            method: 'post',
        })
    
    fetch.form = fetchForm
const entries = {
    index: Object.assign(index, index),
fetch: Object.assign(fetch, fetch),
}

export default entries