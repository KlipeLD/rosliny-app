import PlantController from './PlantController'
import PlantEntryController from './PlantEntryController'
const Controllers = {
    PlantController: Object.assign(PlantController, PlantController),
PlantEntryController: Object.assign(PlantEntryController, PlantEntryController),
}

export default Controllers