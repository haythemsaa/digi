<?php
/**
 * Purchase Requests Controller
 * Manages purchase request workflow and delivery notes
 */

class PurchaseRequests extends Controller {
    private $purchaseRequestModel;
    private $partsModel;

    public function __construct() {
        $this->purchaseRequestModel = $this->model('PurchaseRequest');
        $this->partsModel = $this->model('Parts');
    }

    /**
     * Index - List all purchase requests
     */
    public function index() {
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['priority'])) {
            $filters['priority'] = $_GET['priority'];
        }

        $requests = $this->purchaseRequestModel->getAllRequests($filters);
        $stats = $this->purchaseRequestModel->getRequestStats();

        $data = [
            'page_title' => 'Purchase Requests',
            'requests' => $requests,
            'stats' => $stats
        ];

        $this->view('purchase_requests/index', $data);
    }

    /**
     * Add new purchase request
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Parse items from POST data
            $items = [];
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                $items = $_POST['items'];
            } elseif (isset($_POST['part_id']) && is_array($_POST['part_id'])) {
                // Handle old format
                foreach ($_POST['part_id'] as $index => $partId) {
                    $items[] = [
                        'part_id' => $partId,
                        'description' => $_POST['description'][$index] ?? '',
                        'quantity' => $_POST['quantity'][$index] ?? 1,
                        'unit' => $_POST['unit'][$index] ?? 'pcs',
                        'estimated_price' => $_POST['estimated_price'][$index] ?? 0,
                        'notes' => $_POST['notes'][$index] ?? ''
                    ];
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'At least one item is required';
            } else {
                $requestId = $this->purchaseRequestModel->createRequest($_POST, $items);

                if ($requestId) {
                    $_SESSION['success'] = 'Purchase request created successfully';
                    $this->redirect('purchase_requests/view/' . $requestId);
                } else {
                    $_SESSION['error'] = 'Failed to create purchase request';
                }
            }
        }

        $data = [
            'page_title' => 'New Purchase Request',
            'parts' => $this->partsModel->getAllParts(),
            'departments' => $this->getDepartments()
        ];

        $this->view('purchase_requests/add', $data);
    }

    /**
     * View purchase request details
     */
    public function view($id) {
        $request = $this->purchaseRequestModel->getRequestById($id);

        if (!$request) {
            $_SESSION['error'] = 'Purchase request not found';
            $this->redirect('purchase_requests');
        }

        $data = [
            'page_title' => 'Purchase Request Details',
            'request' => $request
        ];

        $this->view('purchase_requests/view', $data);
    }

    /**
     * Approve purchase request
     */
    public function approve($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->purchaseRequestModel->updateRequestStatus($id, 'approved')) {
                $_SESSION['success'] = 'Purchase request approved successfully';
            } else {
                $_SESSION['error'] = 'Failed to approve purchase request';
            }
        }

        $this->redirect('purchase_requests/view/' . $id);
    }

    /**
     * Reject purchase request
     */
    public function reject($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = $_POST['rejection_reason'] ?? '';

            if ($this->purchaseRequestModel->updateRequestStatus($id, 'rejected', $reason)) {
                $_SESSION['success'] = 'Purchase request rejected';
            } else {
                $_SESSION['error'] = 'Failed to reject purchase request';
            }
        }

        $this->redirect('purchase_requests/view/' . $id);
    }

    /**
     * Convert to purchase order
     */
    public function convertToPO($id) {
        $request = $this->purchaseRequestModel->getRequestById($id);

        if (!$request) {
            $_SESSION['error'] = 'Purchase request not found';
            $this->redirect('purchase_requests');
        }

        if ($request['status'] != 'approved') {
            $_SESSION['error'] = 'Only approved requests can be converted to purchase orders';
            $this->redirect('purchase_requests/view/' . $id);
        }

        // Redirect to procurement to create PO with pre-filled data
        $_SESSION['create_po_from_request'] = $request;
        $this->redirect('procurement/addPurchaseOrder');
    }

    // ==================== DELIVERY NOTES ====================

    /**
     * Delivery notes list
     */
    public function deliveryNotes() {
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $notes = $this->purchaseRequestModel->getAllDeliveryNotes($filters);

        $data = [
            'page_title' => 'Delivery Notes',
            'notes' => $notes
        ];

        $this->view('purchase_requests/delivery_notes', $data);
    }

    /**
     * Add delivery note
     */
    public function addDeliveryNote() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Parse items
            $items = [];
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                $items = $_POST['items'];
            } elseif (isset($_POST['part_id']) && is_array($_POST['part_id'])) {
                foreach ($_POST['part_id'] as $index => $partId) {
                    $items[] = [
                        'part_id' => $partId,
                        'description' => $_POST['description'][$index] ?? '',
                        'quantity_ordered' => $_POST['quantity_ordered'][$index] ?? 0,
                        'quantity_received' => $_POST['quantity_received'][$index] ?? 0,
                        'unit_price' => $_POST['unit_price'][$index] ?? 0,
                        'condition_status' => $_POST['condition_status'][$index] ?? 'good',
                        'notes' => $_POST['item_notes'][$index] ?? ''
                    ];
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'At least one item is required';
            } else {
                $noteId = $this->purchaseRequestModel->createDeliveryNote($_POST, $items);

                if ($noteId) {
                    $_SESSION['success'] = 'Delivery note created successfully';
                    $this->redirect('purchase_requests/viewDeliveryNote/' . $noteId);
                } else {
                    $_SESSION['error'] = 'Failed to create delivery note';
                }
            }
        }

        $supplierModel = $this->model('Supplier');
        $procurementModel = $this->model('Procurement');

        $data = [
            'page_title' => 'New Delivery Note',
            'suppliers' => $supplierModel->getAllSuppliers(),
            'purchase_orders' => $procurementModel->getAllPurchaseOrders(['status' => 'approved']),
            'parts' => $this->partsModel->getAllParts()
        ];

        $this->view('purchase_requests/add_delivery_note', $data);
    }

    /**
     * View delivery note
     */
    public function viewDeliveryNote($id) {
        $note = $this->purchaseRequestModel->getDeliveryNoteById($id);

        if (!$note) {
            $_SESSION['error'] = 'Delivery note not found';
            $this->redirect('purchase_requests/deliveryNotes');
        }

        $data = [
            'page_title' => 'Delivery Note Details',
            'note' => $note
        ];

        $this->view('purchase_requests/view_delivery_note', $data);
    }

    /**
     * Validate delivery note
     */
    public function validateDeliveryNote($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->purchaseRequestModel->updateDeliveryNoteStatus($id, 'validated')) {
                $_SESSION['success'] = 'Delivery note validated and stock updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to validate delivery note';
            }
        }

        $this->redirect('purchase_requests/viewDeliveryNote/' . $id);
    }

    /**
     * Reject delivery note
     */
    public function rejectDeliveryNote($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->purchaseRequestModel->updateDeliveryNoteStatus($id, 'rejected')) {
                $_SESSION['success'] = 'Delivery note rejected';
            } else {
                $_SESSION['error'] = 'Failed to reject delivery note';
            }
        }

        $this->redirect('purchase_requests/viewDeliveryNote/' . $id);
    }

    /**
     * Helper to get departments
     */
    private function getDepartments() {
        // Simple departments list - could be from a database table
        return [
            ['id' => 1, 'name' => 'Maintenance'],
            ['id' => 2, 'name' => 'Operations'],
            ['id' => 3, 'name' => 'Administration'],
            ['id' => 4, 'name' => 'Fleet Management']
        ];
    }
}
