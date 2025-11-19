<?php
/**
 * Stock Controller
 * Manages advanced stock operations with locations, documents, and inventories
 */

class Stock extends Controller {
    private $stockModel;
    private $partsModel;

    public function __construct() {
        $this->stockModel = $this->model('StockDocument');
        $this->partsModel = $this->model('Parts');
    }

    /**
     * Index - Stock overview
     */
    public function index() {
        $stats = $this->stockModel->getStockStats();
        $parts = $this->partsModel->getAllParts();
        $locations = $this->stockModel->getAllLocations();

        $data = [
            'page_title' => 'Stock Management',
            'stats' => $stats,
            'parts' => $parts,
            'locations' => $locations
        ];

        $this->view('stock/index', $data);
    }

    // ==================== LOCATIONS ====================

    /**
     * Stock locations
     */
    public function locations() {
        $locations = $this->stockModel->getAllLocations();

        $data = [
            'page_title' => 'Stock Locations',
            'locations' => $locations
        ];

        $this->view('stock/locations', $data);
    }

    /**
     * Add location
     */
    public function addLocation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->stockModel->createLocation($_POST)) {
                $_SESSION['success'] = 'Stock location created successfully';
                $this->redirect('stock/locations');
            } else {
                $_SESSION['error'] = 'Failed to create stock location';
            }
        }

        $userModel = $this->model('User');

        $data = [
            'page_title' => 'New Stock Location',
            'users' => $userModel->getAllUsers()
        ];

        $this->view('stock/add_location', $data);
    }

    /**
     * Edit location
     */
    public function editLocation($id) {
        $location = $this->stockModel->getLocationById($id);

        if (!$location) {
            $_SESSION['error'] = 'Stock location not found';
            $this->redirect('stock/locations');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->stockModel->updateLocation($id, $_POST)) {
                $_SESSION['success'] = 'Stock location updated successfully';
                $this->redirect('stock/locations');
            } else {
                $_SESSION['error'] = 'Failed to update stock location';
            }
        }

        $userModel = $this->model('User');

        $data = [
            'page_title' => 'Edit Stock Location',
            'location' => $location,
            'users' => $userModel->getAllUsers()
        ];

        $this->view('stock/edit_location', $data);
    }

    // ==================== STOCK DOCUMENTS ====================

    /**
     * Stock documents list
     */
    public function documents() {
        $filters = [];
        if (isset($_GET['document_type'])) {
            $filters['document_type'] = $_GET['document_type'];
        }
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $documents = $this->stockModel->getAllDocuments($filters);

        $data = [
            'page_title' => 'Stock Documents',
            'documents' => $documents
        ];

        $this->view('stock/documents', $data);
    }

    /**
     * Add stock document
     */
    public function addDocument() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Parse items
            $items = [];
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                $items = $_POST['items'];
            } elseif (isset($_POST['part_id']) && is_array($_POST['part_id'])) {
                foreach ($_POST['part_id'] as $index => $partId) {
                    $items[] = [
                        'part_id' => $partId,
                        'quantity' => $_POST['quantity'][$index] ?? 0,
                        'unit_cost' => $_POST['unit_cost'][$index] ?? 0,
                        'notes' => $_POST['item_notes'][$index] ?? ''
                    ];
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'At least one item is required';
            } else {
                $documentId = $this->stockModel->createDocument($_POST, $items);

                if ($documentId) {
                    $_SESSION['success'] = 'Stock document created successfully';
                    $this->redirect('stock/viewDocument/' . $documentId);
                } else {
                    $_SESSION['error'] = 'Failed to create stock document';
                }
            }
        }

        $data = [
            'page_title' => 'New Stock Document',
            'locations' => $this->stockModel->getAllLocations(),
            'parts' => $this->partsModel->getAllParts()
        ];

        $this->view('stock/add_document', $data);
    }

    /**
     * View stock document
     */
    public function viewDocument($id) {
        $document = $this->stockModel->getDocumentById($id);

        if (!$document) {
            $_SESSION['error'] = 'Stock document not found';
            $this->redirect('stock/documents');
        }

        $data = [
            'page_title' => 'Stock Document Details',
            'document' => $document
        ];

        $this->view('stock/view_document', $data);
    }

    /**
     * Validate stock document
     */
    public function validateDocument($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->stockModel->validateDocument($id)) {
                $_SESSION['success'] = 'Stock document validated and stock updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to validate stock document';
            }
        }

        $this->redirect('stock/viewDocument/' . $id);
    }

    /**
     * Cancel stock document
     */
    public function cancelDocument($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = $_POST['cancellation_reason'] ?? '';

            if ($this->stockModel->cancelDocument($id, $reason)) {
                $_SESSION['success'] = 'Stock document cancelled';
            } else {
                $_SESSION['error'] = 'Failed to cancel stock document';
            }
        }

        $this->redirect('stock/viewDocument/' . $id);
    }

    // ==================== PHYSICAL INVENTORIES ====================

    /**
     * Physical inventories list
     */
    public function inventories() {
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $inventories = $this->stockModel->getAllInventories($filters);

        $data = [
            'page_title' => 'Physical Inventories',
            'inventories' => $inventories
        ];

        $this->view('stock/inventories', $data);
    }

    /**
     * Add physical inventory
     */
    public function addInventory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Parse items
            $items = [];
            if (isset($_POST['items']) && is_array($_POST['items'])) {
                $items = $_POST['items'];
            } elseif (isset($_POST['part_id']) && is_array($_POST['part_id'])) {
                foreach ($_POST['part_id'] as $index => $partId) {
                    $items[] = [
                        'part_id' => $partId,
                        'system_quantity' => $_POST['system_quantity'][$index] ?? 0,
                        'counted_quantity' => $_POST['counted_quantity'][$index] ?? 0,
                        'unit_cost' => $_POST['unit_cost'][$index] ?? 0,
                        'notes' => $_POST['item_notes'][$index] ?? ''
                    ];
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'At least one item is required';
            } else {
                $inventoryId = $this->stockModel->createInventory($_POST, $items);

                if ($inventoryId) {
                    $_SESSION['success'] = 'Physical inventory created successfully';
                    $this->redirect('stock/viewInventory/' . $inventoryId);
                } else {
                    $_SESSION['error'] = 'Failed to create physical inventory';
                }
            }
        }

        $data = [
            'page_title' => 'New Physical Inventory',
            'locations' => $this->stockModel->getAllLocations(),
            'parts' => $this->partsModel->getAllParts()
        ];

        $this->view('stock/add_inventory', $data);
    }

    /**
     * View physical inventory
     */
    public function viewInventory($id) {
        $inventory = $this->stockModel->getInventoryById($id);

        if (!$inventory) {
            $_SESSION['error'] = 'Physical inventory not found';
            $this->redirect('stock/inventories');
        }

        $data = [
            'page_title' => 'Physical Inventory Details',
            'inventory' => $inventory
        ];

        $this->view('stock/view_inventory', $data);
    }

    /**
     * Validate physical inventory
     */
    public function validateInventory($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->stockModel->validateInventory($id)) {
                $_SESSION['success'] = 'Physical inventory validated and stock adjusted successfully';
            } else {
                $_SESSION['error'] = 'Failed to validate physical inventory';
            }
        }

        $this->redirect('stock/viewInventory/' . $id);
    }

    /**
     * Get stock by location (AJAX)
     */
    public function getStockByLocation() {
        header('Content-Type: application/json');

        $locationId = $_GET['location_id'] ?? null;

        if ($locationId) {
            // Get all parts with their quantities
            $parts = $this->partsModel->getAllParts();

            // In a real system, you'd filter by location
            // For now, return all parts
            echo json_encode(['success' => true, 'parts' => $parts]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing location ID']);
        }
        exit;
    }

    /**
     * Stock movements report
     */
    public function movements() {
        $filters = [];
        if (isset($_GET['from_date'])) {
            $filters['from_date'] = $_GET['from_date'];
        }
        if (isset($_GET['to_date'])) {
            $filters['to_date'] = $_GET['to_date'];
        }

        $documents = $this->stockModel->getAllDocuments($filters);

        $data = [
            'page_title' => 'Stock Movements',
            'documents' => $documents
        ];

        $this->view('stock/movements', $data);
    }
}
