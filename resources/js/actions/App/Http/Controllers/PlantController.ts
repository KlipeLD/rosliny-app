import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PlantController::index
 * @see app/Http/Controllers/PlantController.php:11
 * @route '/plants'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/plants',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlantController::index
 * @see app/Http/Controllers/PlantController.php:11
 * @route '/plants'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantController::index
 * @see app/Http/Controllers/PlantController.php:11
 * @route '/plants'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PlantController::index
 * @see app/Http/Controllers/PlantController.php:11
 * @route '/plants'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PlantController::index
 * @see app/Http/Controllers/PlantController.php:11
 * @route '/plants'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PlantController::index
 * @see app/Http/Controllers/PlantController.php:11
 * @route '/plants'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PlantController::index
 * @see app/Http/Controllers/PlantController.php:11
 * @route '/plants'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\PlantController::create
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/plants/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlantController::create
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantController::create
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PlantController::create
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PlantController::create
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PlantController::create
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PlantController::create
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\PlantController::store
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/plants',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PlantController::store
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantController::store
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\PlantController::store
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PlantController::store
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\PlantController::show
 * @see app/Http/Controllers/PlantController.php:17
 * @route '/plants/{plant}'
 */
export const show = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/plants/{plant}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlantController::show
 * @see app/Http/Controllers/PlantController.php:17
 * @route '/plants/{plant}'
 */
show.url = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{plant}', parsedArgs.plant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantController::show
 * @see app/Http/Controllers/PlantController.php:17
 * @route '/plants/{plant}'
 */
show.get = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PlantController::show
 * @see app/Http/Controllers/PlantController.php:17
 * @route '/plants/{plant}'
 */
show.head = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PlantController::show
 * @see app/Http/Controllers/PlantController.php:17
 * @route '/plants/{plant}'
 */
    const showForm = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PlantController::show
 * @see app/Http/Controllers/PlantController.php:17
 * @route '/plants/{plant}'
 */
        showForm.get = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PlantController::show
 * @see app/Http/Controllers/PlantController.php:17
 * @route '/plants/{plant}'
 */
        showForm.head = (args: { plant: number | { id: number } } | [plant: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\PlantController::edit
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}/edit'
 */
export const edit = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/plants/{plant}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PlantController::edit
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}/edit'
 */
edit.url = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{plant}', parsedArgs.plant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantController::edit
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}/edit'
 */
edit.get = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PlantController::edit
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}/edit'
 */
edit.head = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PlantController::edit
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}/edit'
 */
    const editForm = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PlantController::edit
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}/edit'
 */
        editForm.get = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PlantController::edit
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}/edit'
 */
        editForm.head = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\PlantController::update
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
export const update = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/plants/{plant}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\PlantController::update
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
update.url = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{plant}', parsedArgs.plant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantController::update
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
update.put = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\PlantController::update
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
update.patch = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\PlantController::update
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
    const updateForm = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PlantController::update
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
        updateForm.put = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\PlantController::update
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
        updateForm.patch = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\PlantController::destroy
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
export const destroy = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/plants/{plant}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PlantController::destroy
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
destroy.url = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{plant}', parsedArgs.plant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PlantController::destroy
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
destroy.delete = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\PlantController::destroy
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
    const destroyForm = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\PlantController::destroy
 * @see app/Http/Controllers/PlantController.php:0
 * @route '/plants/{plant}'
 */
        destroyForm.delete = (args: { plant: string | number } | [plant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const PlantController = { index, create, store, show, edit, update, destroy }

export default PlantController