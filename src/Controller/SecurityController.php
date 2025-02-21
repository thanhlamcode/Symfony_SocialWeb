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
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    private iterable $authServices;

    public function __construct(iterable $authServices)
    {
        $this->authServices = $authServices;
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Nếu có lỗi từ trước, hiển thị flash message
        if ($error) {
            $this->addFlash('error', $error->getMessageKey());
        }

        // Xác định phương thức đăng nhập hợp lệ
        $validMethods = ['password', 'google', 'facebook'];
        $method = $request->query->get('method', 'password');

        if (!in_array($method, $validMethods)) {
            $this->addFlash('error', 'Phương thức đăng nhập không hợp lệ.');
            return $this->redirectToRoute('app_login');
        }

        $user = null;

        // Chỉ xử lý xác thực khi có POST request
        if ($request->isMethod('POST')) {
            try {
                foreach ($this->authServices as $service) {
                    if (str_contains(get_class($service), ucfirst($method) . 'AuthService')) {
                        $user = $service->authenticate($request);
                        break;
                    }
                }

                if ($user) {
                    $this->addFlash('success', 'Đăng nhập thành công!');
                    return $this->redirectToRoute('dashboard');
                } else {
                    $this->addFlash('error', 'Thông tin đăng nhập không hợp lệ.');
                }
            } catch (AuthenticationException $e) {
                $this->addFlash('error', 'Lỗi xác thực: ' . $e->getMessage());
            }
        }

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

    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectGoogle(ClientRegistry $clientRegistry)
    {
        return $clientRegistry->getClient('google')->redirect(['email', 'profile']);
    }

    #[Route('/google/callback', name: 'google_check')]
    public function googleCallback(Request $request, ClientRegistry $clientRegistry, EntityManagerInterface $entityManager): Response
    {
        $client = $clientRegistry->getClient('google');

        try {
            $googleUser = $client->fetchUser();
            $email = $googleUser->getEmail();

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setRoles(['ROLE_USER']);

                $entityManager->persist($user);
                $entityManager->flush();
            }

            return $this->redirectToRoute('dashboard');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Lỗi đăng nhập với Google: ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}

