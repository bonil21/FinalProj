<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/logs')]
#[IsGranted('ROLE_ADMIN')]
class ActivityLogController extends AbstractController
{
    public function __construct(
        private ActivityLogRepository $activityLogRepository
    ) {
    }

    #[Route('', name: 'admin_logs_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userFilter = $request->query->get('user', '');
        $roleFilter = $request->query->get('role', '');
        $actionFilter = $request->query->get('action', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');

        $logs = $this->activityLogRepository->findWithFilters(
            $userFilter,
            $roleFilter,
            $actionFilter,
            $dateFrom ? new \DateTimeImmutable($dateFrom) : null,
            $dateTo ? new \DateTimeImmutable($dateTo) : null
        );

        return $this->render('admin/logs/index.html.twig', [
            'logs' => $logs,
            'userFilter' => $userFilter,
            'roleFilter' => $roleFilter,
            'actionFilter' => $actionFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}

