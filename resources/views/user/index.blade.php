<div>
    <h1>
        <!-- Simplicity is the consequence of refined emotions. - Jean D'Alembert -->
        Utilisateurs:
        @if(count($users) > 0)
            @foreach($users as $user)
                <p>{{ $user->nom }} {{ $user->prenom }}</p>
            @endforeach
        @else
            <p>Aucun utilisateur</p>
        @endif
    </h1>
</div>

<div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <h1>Ajouter un utilisateur</h1>
    <form action="/api/auth/register" method="POST"
        style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
        <input type="text" placeholder="Nom" name="nom">
        <input type="text" placeholder="Prenom" name="prenom">
        <input type="email" placeholder="Email" name="email">
        <input type="password" placeholder="Mot de passe" name="password">
        <button type="submit">Ajouter</button>
    </form>
</div>

<div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <h1>Connexion</h1>
    <form action="/api/auth/login" method="POST"
        style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
        <input type="email" placeholder="Email" name="email">
        <input type="password" placeholder="Mot de passe" name="password">
        <button type="submit">Connexion</button>
    </form>
</div>