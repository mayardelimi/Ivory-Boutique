<?php

namespace App\Controller\Admin;

use App\Entity\Carrier;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function index(): Response
    {
        $totalUsers = $this->entityManager->getRepository(User::class)->count([]);

        $paidOrders = $this->entityManager->getRepository(Order::class)->count(['state' => 1]);
        $unpaidOrders = $this->entityManager->getRepository(Order::class)->count(['state' => 0]);


        $startOfWeek = new \DateTimeImmutable('monday this week 00:00:00');

        $bestSeller = $this->entityManager->getRepository(OrderDetail::class)->createQueryBuilder('od')
            ->select('od.productName as name, SUM(od.productQuantity) as totalSales')
            ->join('od.myOrder', 'o')
            ->where('o.createdAt >= :startOfWeek')
            ->andWhere('o.state = :state')
            ->setParameter('startOfWeek', $startOfWeek)
            ->setParameter('state', 1)
            ->groupBy('od.productName')
            ->orderBy('totalSales', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $sevenDaysAgo = new \DateTimeImmutable('-7 days 00:00:00');

        $incomeData = $this->entityManager->getRepository(OrderDetail::class)->createQueryBuilder('od')
            ->join('od.myOrder', 'o')
            ->select('SUBSTRING(o.createdAt, 1, 10) as date, SUM(od.productQuantity * od.price) as dailyIncome')
            ->where('o.createdAt >= :sevenDaysAgo')
            ->andWhere('o.state = :state')
            ->setParameter('sevenDaysAgo', $sevenDaysAgo)
            ->setParameter('state', 1)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'users' => $totalUsers,
                'paid' => $paidOrders,
                'unpaid' => $unpaidOrders,
            ],
            'best_seller' => $bestSeller,
            'income_chart' => $incomeData,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('la Boutique francaise');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateur', 'fas fa-list' ,    User::class );
        yield MenuItem::linkTo(CategoryCrudController::class, 'Categorie', 'fas fa-list' ,    Category::class );
        yield MenuItem::linkTo(ProductCrudController::class, 'Produit', 'fas fa-list' ,    Product::class );
        yield MenuItem::linkTo(CarrierCrudController::class, 'Transporteur', 'fas fa-list' ,    Carrier::class );
        yield MenuItem::linkTo(OrderCrudController::class, 'Commandes', 'fas fa-list' ,    Order::class );
}
}
