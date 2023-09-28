<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Pagerfanta\Pagerfanta;

class UserController extends AbstractController
{
    /**
     * @throws InvalidArgumentException
     */
    #[Route('/bilemo/users', name: 'app_users', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour consulter les utilisateurs')]
    public function getAllUsers(
        UserRepository $userRepository,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache,
    ): JsonResponse {
        $customer = $this->getUser();
        $adapter = new ArrayAdapter($userRepository->findPublicUsersByCustomer($customer));
        $pager = new Pagerfanta($adapter);
        $idCache = 'getAllUsers-';

        $jsonUserList = $cache->get(
            $idCache,
            function (ItemInterface $item) use ($serializer, $pager) {
                $pagerFanta = new PagerfantaFactory();
                echo('L\'élément n\'est pas encore en cache !');
                $item->tag('usersCache')
                    ->expiresAfter(60);
                $userList
                    = $pagerFanta->createRepresentation(
                        $pager,
                        new HateoasRoute('app_users', [], true),
                        new CollectionRepresentation($pager->getCurrentPageResults())
                    );

                return $serializer->serialize($userList, 'json');
            }
        );

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    #[Route('/bilemo/users/{id}', name: 'app_users_details', methods: ['GET'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour consulter un utilisateur')]
    public function getUserDetails(
        User $user,
        SerializerInterface $serializer,
    ): JsonResponse {
        $context = SerializationContext::create()->setGroups(array('getUsers'));
        $jsonUser = $serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/bilemo/users/create', name: 'app_users_create', methods: ['POST'])]
    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur')]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $customer = $this->getUser();
        $user->setCustomer($customer);

        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $cache->invalidateTags(['usersCache']);

        $context = SerializationContext::create()->setGroups(array('getUsers'));
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate(
            'app_users_details',
            ['id' => $user->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route('/bilemo/users/delete/{id}', name: 'app_users_delete', methods: ['DELETE'])]
    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants pour supprimer un utilisateur')]
    public function deleteUser(
        User $user,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $entityManager->remove($user);
        $entityManager->flush();

        $cache->invalidateTags(['usersCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
