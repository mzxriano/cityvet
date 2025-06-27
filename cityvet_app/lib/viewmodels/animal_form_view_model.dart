import 'package:cityvet_app/models/animal_model.dart';

class AnimalFormViewModel {

  AnimalModel? _animalModel;

  AnimalModel get animalModel => _animalModel!;

  setAnimalModel(AnimalModel animalModel) {
    _animalModel = animalModel;
  }

  Future<void> createAnimal() async {
    
  }

}