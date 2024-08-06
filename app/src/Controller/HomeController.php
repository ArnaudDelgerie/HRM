<?php

namespace App\Controller;

use App\Repository\DayLeaveRequestRepository;
use DateTime;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Umulmrum\Holiday\Filter\IncludeTimespanFilter;
use Umulmrum\Holiday\HolidayCalculator;
use Umulmrum\Holiday\Provider\France\France;

class HomeController extends AbstractController
{
    public function __construct() {} 

    #[Route('/', name: 'app_home')]
    public function index(): Response 
    {
        return $this->render('/home/index.html.twig');
    }

    #[Route('/calendar/allday/events', name: 'app_calendar_all_day_events')]
    public function getAllDayEvents(Request $request, DayLeaveRequestRepository $dayLeaveRequestRepository, NormalizerInterface $normalizer): Response
    {
        $stringDate = $request->get('date', (new DateTime())-> format(DateTime::ATOM));
        $date = new DateTime($stringDate);

        $start = clone $date;
        $start->modify('Monday this week');

        $end = clone $date;
        $end->modify('Sunday this week');

        $calculator = new HolidayCalculator();
        $holidays = $calculator->calculate(France::class, (int)$date->format('Y'));
        $holidays = $holidays->filter(new IncludeTimespanFilter($start, $end));
        $holidays = $normalizer->normalize($holidays->getList(), 'json');

        $dayLeaveRequests = $dayLeaveRequestRepository->getDayLeaveRequests($start, $end);
        $dayLeaveRequests = $normalizer->normalize($dayLeaveRequests, 'json');

        return $this->json([
            'success' => true,
            'dayLeaveRequests' => $dayLeaveRequests,
            'holidays' => $holidays,
        ]);
    }
}

