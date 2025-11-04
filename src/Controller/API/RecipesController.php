<?php

namespace App\Controller\API;

use App\Dto\PaginationDto;
use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;

class RecipesController extends AbstractController
{
    #[Route("/api/recipes", methods: ['GET'])]
    public function index(
        RecipeRepository $repository,
//        Request $request,
//        SerializerInterface $serializer,
        #[MapQueryString]
        PaginationDto $paginationDto
    )
    {
        $recipes = $repository->paginateRecipes($paginationDto->page);
//        $recipes = $repository->paginateRecipes($request->query->get('page', 1));
//        dd($serializer->serialize($recipes, 'xml', [
//            'groups' => ['recipes.index']
//        ]));

        return $this->json($recipes, 200, [], [
            'groups' => ['recipes.index']
        ]);
    }

    #[Route("/api/recipes/{id}", requirements: ['id' => Requirement::DIGITS])]
    public function show(Recipe $recipe)
    {
        return $this->json($recipe, 200, [], [
            'groups' => ['recipes.index', 'recipes.show']
        ]);
    }

//    /** First version */
//    #[Route("/api/recipes", methods: ['POST'])]
//    public function create(Request $request, SerializerInterface $serializer)
//    {
//        $recipe = new Recipe();
//        $recipe->setCreatedAt(new \DateTimeImmutable());
//        $recipe->setUpdatedAt(new \DateTimeImmutable());
//        dd($serializer->deserialize($request->getContent(), Recipe::class, 'json', [
//            AbstractNormalizer::OBJECT_TO_POPULATE => $recipe, // use existing $recipe instead of create a new one from API request
//            'groups' => ['recipes.create']
//        ]));
//    }

    /** Second version */
    #[Route("/api/recipes", methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            serializationContext: ['groups' => ['recipes.create']]
        )]
        Recipe $recipe,
        EntityManagerInterface $em
    ) {
        $recipe->setCreatedAt(new \DateTimeImmutable());
        $recipe->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($recipe);
        $em->flush();

        return $this->json($recipe, 201, [], [
            'groups' => ['recipes.index', 'recipes.show']
        ]);
    }
}
