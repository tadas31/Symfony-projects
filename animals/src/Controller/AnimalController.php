<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\Family;
use App\Repository\AnimalRepository;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Rest\Route("/api/animal", name="animal.")
 */
class AnimalController extends AbstractFOSRestController
{
    private $animalRepository;
    private $familyRepository;
    private $entityManager;

    public function __construct(AnimalRepository $animalRepository, FamilyRepository $familyRepository, EntityManagerInterface $entityManager)
    {
        $this->animalRepository = $animalRepository;
        $this->familyRepository = $familyRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Get("/", name="index")
     */
    public function index() {
        $animals =  $this->animalRepository->findAll();

        if (sizeof($animals) > 0) {
            return $this->view($this->serializeAnimals($animals), 200);
        }

        return $this->view([
            'code' => 404,
            'message' => 'Animals not found'
        ], 404);
    }

    /**
     * @Rest\Get("/{id}", name="show")
     */
    public function show(Animal $animal) {
        return $this->view($this->serializeAnimals($animal), 200);
    }

    /**
     * @Rest\Get("/family/{id}", name="show.byFamily")
     */
    public function showAnimalsByFamily(Family $family) {
        $animals = $family->getAnimals();
        if (sizeof($animals) > 0) {
            return $this->view($this->serializeAnimals($animals), 200);
        }

        return $this->view([
            'code' => 404,
            'message' => 'Animals not found'
        ], 404);
    }

    /**
     * @Rest\Post("/", name="create")
     * @Rest\RequestParam(name="name", description="Animal species name", nullable=false, requirements="[a-zA-Z\s]+")
     * @Rest\RequestParam(name="description", description="Animal description", nullable=true)
     * @Rest\RequestParam(name="family_id", description="Id of family animal belongs to", nullable=true, requirements="\d+")
     */
    public function create(ParamFetcher $paramFetcher) {
        $name = $paramFetcher->get('name');
        $description = $paramFetcher->get('description');
        $family_id = $paramFetcher->get('family_id');

        $animal = new Animal();
        $animal->setName($name);
        $animal->setDescription($description);
        if ($family_id != null)
            $animal->setFamily($this->familyRepository->find($family_id));

        $this->entityManager->persist($animal);
        $this->entityManager->flush();

        return $this->view($this->serializeAnimals($animal), 201);
    }

    /**
     * @Rest\Put("/{id}", name="update")
     * @Rest\RequestParam(name="name", description="Animal species name", nullable=false, requirements="[a-zA-Z\s]+")
     * @Rest\RequestParam(name="description", description="Animal description", nullable=true)
     * @Rest\RequestParam(name="family_id", description="Id of family animal belongs to", nullable=true, requirements="\d+")
     */
    public function update(ParamFetcher $paramFetcher, Animal $animal) {
        $name = $paramFetcher->get('name');
        $description = $paramFetcher->get('description');
        $family_id = $paramFetcher->get('family_id');

        $animal->setName($name);
        $animal->setDescription($description);
        if ($family_id != null) 
            $animal->setFamily($this->familyRepository->find($family_id));
        else 
            $animal->setFamily(null);

        $this->entityManager->persist($animal);
        $this->entityManager->flush();

        return $this->view(null, 204);
    }

    /**
     * @Rest\Delete("/{id}", name="delete")
     */
    public function delete(Animal $animal) {
        $this->entityManager->remove($animal);
        $this->entityManager->flush();

        return $this->view(null, 204);
    }

    /**
     * Formats animal response json.
     */
    public function serializeAnimals($animals) {
        $serializer = new Serializer([new ObjectNormalizer()]);
        return $serializer->normalize($animals, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'name', 'description', 'family' => ['id', 'name']]
        ]);
    }
}