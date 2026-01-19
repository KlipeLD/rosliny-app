<form method="POST" action="{{ route('plants.entries.fetch', $plant) }}">
  @csrf
  <button type="submit">Pobierz dane z API</button>
</form>
