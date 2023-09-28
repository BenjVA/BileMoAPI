<?php

namespace App\Controller;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class CustomerController extends AbstractController
{
    /**
     * This method create a customer manually
     *
     * @OA\Response(
     *     response=201,
     *     description="Create a customer",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Customer::class))
     *     )
     * )
     * @OA\Tag(name="Customer")
     *
     * @param EntityManagerInterface      $entityManager
     * @param UserPasswordHasherInterface $passwordHasher
     * @param SerializerInterface         $serializer
     *
     * @return JsonResponse
     */
    #[Route('/bilemo/customer/create', name: 'app_customer_create', methods: ['POST'])]
    public function createCustomer(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SerializerInterface $serializer
    ): JsonResponse {
        $customer = new Customer();
        $customer->setName('Customerdemo')
            ->setEmail('customer@demo.com')
            ->setPassword($passwordHasher->hashPassword($customer, 'password'))
            ->setRoles(['ROLE_USER']);

        $entityManager->persist($customer);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(array('getCustomer'));
        $jsonCustomer = $serializer->serialize($customer, 'json', $context);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, [], true);
    }
}
