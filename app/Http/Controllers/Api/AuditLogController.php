<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends BaseController
{
    public function index(Request $request)
    {
        abort_if(!request()->user()->can('view_audit'), 403, 'Unauthorized action.');
        
        $query = AuditLog::with('user:id,name,email')->orderBy('created_at', 'desc');
        
        return $this->paginatedResponse($query->paginate(20), 'Audit logs retrieved');
    }
}
