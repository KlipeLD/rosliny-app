import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
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
* @see \App\Http\Controllers\PlantEntryController::fetchFromApi
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
export const fetchFromApi = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: fetchFromApi.url(args, options),
    method: 'post',
})

fetchFromApi.definition = {
    methods: ["post"],
    url: '/plants/{plant}/entries/fetch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PlantEntryController::fetchFromApi
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
fetchFromApi.url = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return fetchFromApi.definition.url
            .replace('{plant}', parsedArgs.plant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantEntryController::fetchFromApi
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
fetchFromApi.post = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: fetchFromApi.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PlantEntryController::fetchFromApi
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
    const fetchFromApiForm = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: fetchFromApi.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PlantEntryController::fetchFromApi
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/plants/{plant}/entries/fetch'
 */
        fetchFromApiForm.post = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: fetchFromApi.url(args, options),
            method: 'post',
        })
    
    fetchFromApi.form = fetchFromApiForm
/**
* @see \App\Http\Controllers\PlantEntryController::edit
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/entries/{entry}/edit'
 */
export const edit = (args: { entry: string | number } | [entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/entries/{entry}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlantEntryController::edit
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/entries/{entry}/edit'
 */
edit.url = (args: { entry: string | number } | [entry: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { entry: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    entry: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        entry: args.entry,
                }

    return edit.definition.url
            .replace('{entry}', parsedArgs.entry.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantEntryController::edit
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/entries/{entry}/edit'
 */
edit.get = (args: { entry: string | number } | [entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PlantEntryController::edit
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/entries/{entry}/edit'
 */
edit.head = (args: { entry: string | number } | [entry: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PlantEntryController::edit
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/entries/{entry}/edit'
 */
    const editForm = (args: { entry: string | number } | [entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PlantEntryController::edit
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/entries/{entry}/edit'
 */
        editForm.get = (args: { entry: string | number } | [entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PlantEntryController::edit
 * @see app/Http/Controllers/PlantEntryController.php:0
 * @route '/entries/{entry}/edit'
 */
        editForm.head = (args: { entry: string | number } | [entry: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
/**
* @see \App\Http\Controllers\PlantEntryController::update
 * @see app/Http/Controllers/PlantEntryController.php:17
 * @route '/entries/{entry}'
 */
export const update = (args: { entry: number | { id: number } } | [entry: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/entries/{entry}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\PlantEntryController::update
 * @see app/Http/Controllers/PlantEntryController.php:17
 * @route '/entries/{entry}'
 */
update.url = (args: { entry: number | { id: number } } | [entry: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { entry: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { entry: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    entry: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        entry: typeof args.entry === 'object'
                ? args.entry.id
                : args.entry,
                }

    return update.definition.url
            .replace('{entry}', parsedArgs.entry.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantEntryController::update
 * @see app/Http/Controllers/PlantEntryController.php:17
 * @route '/entries/{entry}'
 */
update.patch = (args: { entry: number | { id: number } } | [entry: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\PlantEntryController::update
 * @see app/Http/Controllers/PlantEntryController.php:17
 * @route '/entries/{entry}'
 */
    const updateForm = (args: { entry: number | { id: number } } | [entry: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PlantEntryController::update
 * @see app/Http/Controllers/PlantEntryController.php:17
 * @route '/entries/{entry}'
 */
        updateForm.patch = (args: { entry: number | { id: number } } | [entry: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const PlantEntryController = { index, fetchFromApi, edit, update }

export default PlantEntryController