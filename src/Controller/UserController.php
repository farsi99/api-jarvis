<?php

namespace App\Controller;

use App\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use \App\Exception\RessourceValidationException;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;

class UserController extends FOSRestController
{
    /**
     * cette méthode traite l'affchage de tous les users
     * @Rest\Get("/users", name="vue_users")
     */
    public function usersAction(UserRepository $repo)
    {
        $user = $repo->findAll();
        if ($user) {
            return $this->view($user, Response::HTTP_OK);
        }
        throw new RessourceValidationException("Cette base n'a pas encore de user");
    }

    /**
     * cette méthode traite l'affichage d'un détail user
     * @Rest\Get("/users/{id}", name="show_user")
     */
    public function userAction(int $id, UserRepository $repo)
    {
        $user = $repo->findOneBy(['id' => $id]);
        if ($user) {
            return $this->view($user, Response::HTTP_OK);
        }
        throw new RessourceValidationException("L'identifiant n° $id n'est pas associé à un user");
    }

    /**
     * cette méthode traite l'ajout d'un user
     * @Rest\Post("/users", name="post_user")
     */
    public function creatAction(ObjectManager $em, Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        $validation = $this->get('validator')->validate($user);
        if (count($validation)) {
            return $this->view($validation, Response::HTTP_BAD_REQUEST);
        }
        $em->persist($user);
        $em->flush();
        return $this->view($user, Response::HTTP_CREATED, [
            'message' => 'Enregistrement effectué avec succès',
            'location' => $this->generateUrl('show_user', ['id' => $user->getId()])
        ]);
    }

    /**
     * cette méthode traite la modification d'un user
     * @Rest\Put("/users/{id}", name="update_user")
     */
    public function updateAction(ObjectManager $em, Request $request, UserRepository $repo, int $id)
    {
        $user = $repo->findOneBy(['id' => $id]);
        if ($user) {
            $form = $this->createForm(UserType::class, $user);
            $data = json_decode($request->getContent(), true);
            $form->submit($data);
            $validation = $this->get('validator')->validate($user);
            if (count($validation)) {
                return $this->view($validation, Response::HTTP_BAD_REQUEST);
            }
            $em->persist($user);
            $em->flush();
            return $this->view($user, Response::HTTP_CREATED, [
                'message' => 'Modification effectué avec succès',
                'location' => $this->generateUrl('show_user', ['id' => $id])
            ]);
        } else {
            throw new RessourceValidationException("L'identifiant n° $id n'est pas associé à un user");
        }
    }

    /**
     * cette méthode traite la suppression des donénes
     * @Rest\Delete("/users/{id}", name="delete_user")
     */
    public function delete(ObjectManager $em, int $id, UserRepository $repo)
    {
        $user = $repo->findOneBy(['id' => $id]);
        if ($user) {
            $em->remove($user);
            $em->flush();
            return $this->view(['Suprression effectuée avec succès'], Response::HTTP_OK);
        }
        throw new RessourceValidationException("L'identifiant n° $id n'est pas associé à un user");
    }
}
