<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Kiểm tra email đã tồn tại chưa
                $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
                if ($existingUser) {
                    $this->addFlash('error', 'Email đã tồn tại. Vui lòng chọn email khác.');
                    return $this->redirectToRoute('app_register');
                }

                // Mã hóa mật khẩu
                $hashedPassword = $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_USER']);

                // Lưu vào database
                $entityManager->persist($user);
                $entityManager->flush();

                // Thông báo thành công
                $this->addFlash('success', 'Đăng ký thành công! Hãy đăng nhập.');
                return $this->redirectToRoute('app_login');
            } else {
                // Nếu form không hợp lệ, hiển thị thông báo lỗi
                $this->addFlash('error', 'Có lỗi trong quá trình đăng ký. Vui lòng kiểm tra lại.');
            }
        }

        return $this->render('register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}

