<?php

namespace App\Controller;

use App\Entity\Family;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Rest\Route("/api/family", name="family.")
 */
class FamilyController extends AbstractFOSRestController
{
    private $familyRepository;
    private $entityManager;

    public function __construct(FamilyRepository $familyRepository, EntityManagerInterface $entityManager)
    {
        $this->familyRepository = $familyRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Get("/", name="index")
     */
    public function index() {
        $families = $this->familyRepository->findAll();

        if (sizeof($families) > 0) {
            return $this->view($this->serializeFamilies($families), 200);
        }

        return $this->view([
            'code' => 404,
            'message' => 'Families not found'
        ], 404);
    }
    
    /**
     * @Rest\Post("/", name="create")
     * @Rest\RequestParam(name="name", description="Animal family name", nullable=false, requirements="[a-zA-Z\s]+")
     */
    public function create(ParamFetcher $paramFetcher) {
        $name = $paramFetcher->get('name');

        $family = new Family();
        $family->setName($name);

        $this->entityManager->persist($family);
        $this->entityManager->flush();

        return $this->view($this->serializeFamilies($family), 201);
    }

    /**
     * @Rest\Put("/{id}", name="update")
     * @Rest\RequestParam(name="name", description="Animal family name", nullable=false, requirements="[a-zA-Z\s]+")
     */
    public function update(ParamFetcher $paramFetcher, Family $family) {
        $name = $paramFetcher->get('name');

        $family->setName($name);

        $this->entityManager->persist($family);
        $this->entityManager->flush();

        return $this->view(null, 204);
    }

    /**
     * @Rest\Delete("/{id}", name="delete")
     */
    public function delete(Family $family) {
        $this->entityManager->remove($family);
        $this->entityManager->flush();

        return $this->view(null, 204);
    }

    /**
     * Formats family response json.
     */
    public function serializeFamilies($families) {
        $serializer = new Serializer([new ObjectNormalizer()]);
        return $serializer->normalize($families, null, [
            AbstractNormalizer::ATTRIBUTES => ['id', 'name']
        ]);
    }
}
