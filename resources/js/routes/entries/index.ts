import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
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
const entries = {
    edit: Object.assign(edit, edit),
update: Object.assign(update, update),
}

export default entries