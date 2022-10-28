import math
import numpy as np
import scipy.linalg as nla

def _v_ (T, mu):
    # Algorithme du calcul du nombre de valeurs propres de T en dessous de la valeur mu
    # T est une matrice tridiagonale
    # mu est une valeur palié
    
    # On récupère la taille de T
    m = T.shape[0]
    
    # On initialise les données du théorème de Gershgorin
    bim1 = 0
    sim1 = 1
    fim1 = 1
    nb = 0
    
    # On itère pour toute la matrice
    for i in range(m-1):
        if bim1 == 0:
            fi = (T[i, i] - mu)*sim1
        elif bim2 == 0:
            fi = (T[i, i] - mu) * fim1 - (bim1**2) * sim2
        else:
            fi = (T[i, i] - mu) * fim1 - (bim1**2) * fim2
        
        if fi > 0: si = 1
        elif fi < 0: si = -1
        else: si = sim1
        
        if si*sim1 < 0:
            nb+=1
        
        fim2 = fim1
        fim1 = fi
        sim2 = sim1
        sim1 = si
        bim2 = bim1
        bim1 = T[i+1, i]
        
    # On réalise la dernière étape, impossible dans la boucle à cause des indexes sur les bi
    if bim1 == 0:
        fi = (T[m-1, m-1] - mu)*sim1
    elif bim2 == 0:
        fi = (T[m-1, m-1] - mu) * fim1 - (bim1**2) * sim2
    else:
        fi = (T[m-1, m-1] - mu) * fim1 - (bim1**2) * fim2
    
    if fi > 0: si = 1
    elif fi < 0: si = -1
    else: si = sim1

    if si*sim1 < 0:
        nb+=1
    
    # On retourne le nombre de valeurs propres
    return nb
    

#M = np.array([[1, 1, 0, 0], [1, 1, 1, 0], [0, 1, 2, 1], [0, 0, 1, 3]])
#print(_v_(M, 1.22) - _v_(M, -0.29)) Retourne 2
    
def _isolate_eigenvalues_ (T, a, b, sigma):
    # Algorithmes d'isolation des valeurs propres dans des intervalles à l'aide de Gershgorin
    # T est une matrice tridiagonale de données
    # a et b sont les bornes dans lesquelles on doit borner les valeurs propres
    # sigma est la précision de la borne des valeurs propres
    
    n = _v_(T,b) - _v_(T,a)
    if n == 0:
        return []
    elif n == 1 and (b-a < sigma):
        return [[a, b]]
    else:
        m = (a+b) / 2
        L1 = _isolate_eigenvalues_(T, a, m, sigma)
        L2 = _isolate_eigenvalues_(T, m, b, sigma)
        return L1 + L2

#print(_isolate_eigenvalues_ (M, -1, 4, 0.0000000001))

def _borne_de_gershgorin_ (T):
    # Algorithmes du calcul de la borne sur tous les disques de Gershgorin
    # T est une matrice tridiagonale de données
    
    m, n = T.shape
    centre = T[0, 0]
    somme = T[0, 1]
    disque = [centre - somme, centre + somme]
    mini = disque[0]
    maxi = disque[1]
    for i in range(1, m):
        centre = T[i,i]
        somme = 0
        for j in range(n):
            if j != i:
                somme += T[i, j]
        disque = [centre - somme, centre + somme]
        if mini > disque[0]:
            mini = disque[0]
        if maxi < disque[1]:
            maxi = disque[1]
    return [mini, maxi]

#print(_borne_de_gershgorin_(M)) retourne [-1, 4]

def _eigenvalues_ (T, sigma):
    # Algorithmes du calcul des valeurs propres à partir de Gershgorin
    # T est une matrice tridiagonale de données
    # sigma est la précision de l'approximation des valeurs propres
    
    B = _borne_de_gershgorin_(T)
    mu = []
    C = _isolate_eigenvalues_(T, B[0], B[1], sigma)
    for elt in C:
        mu.append((elt[0]+elt[1])/2)
    return mu

