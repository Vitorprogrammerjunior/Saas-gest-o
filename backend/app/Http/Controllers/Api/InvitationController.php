<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WorkspaceInvitation;
use App\Models\Invitation;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Send invitation — O(1)
     */
    public function invite(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['nullable', 'in:admin,member'],
        ]);

        // Check if already a member
        $isMember = $workspace->members()->where('email', $validated['email'])->exists();
        if ($isMember) {
            return response()->json(['message' => 'Este usuário já é membro do workspace.'], 422);
        }

        // Check for pending invitation
        $existingInvite = Invitation::where('email', $validated['email'])
            ->where('workspace_id', $workspace->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvite) {
            return response()->json(['message' => 'Já existe um convite pendente para este email.'], 422);
        }

        $invitation = Invitation::create([
            'email' => $validated['email'],
            'workspace_id' => $workspace->id,
            'invited_by' => $request->user()->id,
            'role' => $validated['role'] ?? 'member',
        ]);

        Mail::to($validated['email'])->send(new WorkspaceInvitation($invitation));

        return response()->json([
            'message' => 'Convite enviado com sucesso.',
            'invitation' => $invitation,
        ], 201);
    }

    /**
     * Accept invitation — O(1)
     */
    public function accept(Request $request, string $token): JsonResponse
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            return response()->json(['message' => 'Este convite expirou.'], 410);
        }

        $user = $request->user();

        // Verify the invitation email matches the authenticated user
        if ($user->email !== $invitation->email) {
            return response()->json(['message' => 'Este convite não pertence a você.'], 403);
        }

        // Add user to workspace
        $invitation->workspace->members()->attach($user->id, [
            'role' => $invitation->role,
        ]);

        $invitation->update(['status' => 'accepted']);

        return response()->json([
            'message' => 'Convite aceito! Você agora faz parte do workspace.',
            'workspace' => $invitation->workspace,
        ]);
    }

    /**
     * List pending invitations for a workspace — O(k)
     */
    public function index(Workspace $workspace): JsonResponse
    {
        $this->authorize('view', $workspace);

        $invitations = $workspace->invitations()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with('inviter:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($invitations);
    }

    /**
     * Cancel invitation — O(1)
     */
    public function cancel(Request $request, Workspace $workspace, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $workspace);

        $invitation->update(['status' => 'declined']);

        return response()->json(['message' => 'Convite cancelado.']);
    }
}
