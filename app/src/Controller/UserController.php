<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Trait\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    use PaginatorTrait;

    public function __construct() {}

    #[Route('/user', name: 'app_user')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $page = (int) $request->get('page', 1);
        $limit = $this->getParameter('paginator_limit');

        $usersQuery = $userRepository->getUsers();
        $userPaginator = $this->paginate($usersQuery, $page, $limit);
        $paginationData = $this->getPaginationData($userPaginator, $request, $page, $limit);
        dump($paginationData); 
        return $this->render('user/index.html.twig', [
            'users' => $userPaginator->getIterator(),
            'paginationData' => $paginationData,
        ]);
    }
}
