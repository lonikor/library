<?php

namespace App\Controller\Api\V1;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Requests\BookRequestData;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/v1/books')]
class BookController extends AbstractController
{
    private const CONTEXT = [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'];

    public function __construct(
        private readonly BookRepository $bookRepository,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route(methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->bookRepository->getAllSortedByName(),
            context: static::CONTEXT
        );
    }

    #[Route(path: '/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $book = $this->bookRepository->find($id);

        if (null === $book) {
            throw $this->createNotFoundException('Book not found.');
        }

        return $this->json($book, context: static::CONTEXT);
    }

    #[Route(methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function store(Request $request): JsonResponse
    {
        /** @var BookRequestData $requestData */
        $requestData = $this->serializer->deserialize(
            $request->getContent(),
            BookRequestData::class,
            'json'
        );

        $constraintViolationList = $this->validator->validate($requestData);

        if ($constraintViolationList->count() > 0) {
            return $this->generateErrorResponse($constraintViolationList);
        }

        $book = new Book();
        $book->setName($requestData->getName());
        $book->setIsbn($requestData->getIsbn());
        $book->setAuthor($requestData->getAuthor());
        $book->setPublicationYear($requestData->getPublicationYear());

        $this->entityManager->persist($book);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['message' => 'Book already exists.']);
        }

        return $this->json($book, 201, context: static::CONTEXT);
    }

    #[Route(path: '/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $requestData = $this->serializer->deserialize(
            $request->getContent(),
            BookRequestData::class,
            'json'
        );

        $constraintViolationList = $this->validator->validate($requestData);

        if ($constraintViolationList->count() > 0) {
            return $this->generateErrorResponse($constraintViolationList);
        }

        $book = $this->bookRepository->find($id);

        if (null === $book) {
            throw $this->createNotFoundException('Book not found.');
        }

        $book->setName($requestData->getName());
        $book->setIsbn($requestData->getIsbn());
        $book->setAuthor($requestData->getAuthor());
        $book->setPublicationYear($requestData->getPublicationYear());

        $this->entityManager->flush();

        return $this->json($book, context: static::CONTEXT);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $book = $this->bookRepository->find($id);

        if (null === $book) {
            throw $this->createNotFoundException('Book not found.');
        }

        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }

    private function generateErrorResponse(ConstraintViolationList $constraintViolationList): JsonResponse
    {
        $errors = [];

        /** @var ConstraintViolation $item */
        foreach ($constraintViolationList as $item) {
            $errors[] = [
                'target' => $item->getPropertyPath(),
                'error' => $item->getMessage(),
            ];
        }

        return $this->json($errors, 400);
    }
}
