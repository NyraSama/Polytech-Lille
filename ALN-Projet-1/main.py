import math
import numpy as np
import scipy.linalg as nla
import matplotlib.pyplot as plt
from puissance_inverse import _PuissanceInverse_
from algo_qr.algo_qr_initial import _VPAlgoQR_
from theoreme_gershgorin import _eigenvalues_

# Affichage plus agréable
np.set_printoptions(linewidth=240)

# Le document original comporte 15 variables.
# J'en ai retenu 12.

A = np.array ([
    [3, 97.8, 119, 2.6, 7.4, 0.2, 1.8, 2.2, 53, 34, 28.3, 21],
    [0.2, 80.5, 121, 2.1, 5.3, 0.5, 1.4, 1.4, 47.4, 20.5, 27.8, 19.9],
    [0.4, 6.1, 67, 4.2, 9.9, 3, 1.5, 2.5, 10.6, 21, 15.1, 23.1],
    [7.3, 106.4, 129, 1.9, 14.7, 1.1, 2.1, 3.7, 45.9, 12.5, 22.1, 29.9],
    [4.6, 170.6, 79, 1, 26.4, -4.4, 1.4, 11.8, 27.6, 23, 26, 31],
    [6.7, 69.3, 98, 2.4, 26.2, -1.4, 1.4, 4.9, 32.7, 35, 22.7, 27],
    [3.7, 86, 108, 2.2, 10.6, 0.1, 2, 2, 45.4, 34, 30.8, 19.3],
    [2.1, 120.7, 100, 3.3, 11.7, -1, 1.4, 4.6, 35.6, 33, 27.8, 24.5],
    [4.5, 71.1, 94, 3.1, 14.7, -3.5, 1.4, 12, 20.7, 10, 18.4, 23.5],
    [0.9, 18.3, 271, 2.9, 5.3, 0.5, 1.5, 2.3, 50.7, 22, 20.1, 16.8], 
    [2.9, 70.9, 85, 3.2, 7, 1.5, 1.5, 4, 25, 35, 18.9, 21.4],
    [3.6, 65.5, 131, 2.8, 6, -0.6, 1.8, 1.6, 55.7, 34, 28.4, 15.7],
    [2.5, 72.4, 129, 2.6, 4.9, 0.7, 1.4, 1.7, 45.6, 34, 28.2, 16.9],
    [4.9, 108.1, 77, 2.8, 17.6, -1.9, 1.4, 6, 14.8, 25, 24.3, 24.4],
    [5.1, 46.9, 84, 2.8, 10.2, -2, 1.6, 4.9, 18.5, 20, 21.5, 19.3],
    [3.3, 43.3, 73, 3.7, 14.9, 1.1, 1.5, 2.1, 10.1, 19, 16, 20.6],
    [1.5, 49, 114, 3.2, 7.9, 0.3, 1.8, 1.6, 46.8, 26, 26.3, 17.9]],
    dtype=np.float64)

m, n = A.shape

lignes = ['BE', 'DE', 'EE', 'IE', 'EL', 'ES', 'FR', 'IT', 'CY', 'LU', 'MT', 'NL', 'AT', 'PT', 'SL', 'SK', 'FI']
colonnes = [
'Déficit public / PIB (estimation 2013)', 
'Dette / PIB en %', 
'PIB / habitant', 
'inflation fin 2012', 
'taux de chômage M01 en 2013', 
'taux de croissance du PIB réel (prévision 2013)', 
'taux de fécondité', 
'taux d\'emprunt à 10 ans', 
'coût main d\'œuvre / produits manufacturés', 
'impôt sur les sociétés', 
'prélèvements sociaux', 
'% de pauvreté ou d\'exclusion sociale']
            
def _1_centrage (M):
    # Algorithme de centrage des données
    # M est la matrice des données socio-économiques sur les pays de la zone euro
    
    # On récupère le nombre de colonnes de M
    l, c = M.shape
    
    # On soustrait aux coefficients de chaque colonne de M la moyenne de la colonne correspondante
    for col in range(c):
        meanCol = np.mean(M[:, col])
        M[:, col] -= meanCol
            
def _2_sigmaCol (col):
    # Algorithme de calcul de sigma l'écart-type
    # col est le vecteur correspondant à une colonne de matrice dont il faut calculer l'écart-type
    
    # On retourne directement l'écart-type à l'aide d'une formule valable pour des données centrées
    return ((1/col.shape[0]) * np.dot(col, col))**0.5
            
def _2_adimention (M):
    # Algorithme d'adimmensionnement des données
    # M est la matrice des données socio-économiques sur les pays de la zone euro
    
    # On récupère le nombre de colonnes de M
    l, c = M.shape
    
    # On divise les coefficients de chaque colonne de M par l'écart-type de la colonne correspondante
    for col in range(c):
        mi = M[:, col]
        sigmaCol = _2_sigmaCol(mi)
        M[:, col] /= sigmaCol

def _3_covariance (M):
    # Algorithme de calcul de la matrice de covariance
    # M est la matrice des données socio-économiques sur les pays de la zone euro
    
    # On récupère le nombre de colonnes de M
    l, c = M.shape
    
    # On retourne la matrice de covariance de M
    return (1/(c-1))*np.dot(np.transpose(M), M)

def _4_5_ValVectPropre_Initial (M):
    # Algorithme de calcul des deux plus grandes valeurs propres et de leurs vecteurs propres associés
    # M est la matrice de covariance de la matrice des données socio-économiques sur les pays de la zone euro
    
    # On récupère les valeurs propres et les vecteurs propres de M
    V = nla.eig(M)
    
    # On divise notre récupération en un vecteur de valeur propre et un de vecteur propre
    B = V[0]
    C = V[1]
    
    # On initialise un maximum de valeurs propres au deux premières récupéré (on garde les indices)
    if B[0] > B[1]:
        maxi = [0, 1]
    else:
        maxi = [1, 0]
    
    # On teste tout le reste des valeurs propres pour savoir si il n'ya pas plus grand que celles récupérées
    for k in range(2, B.shape[0]):
        if B[k] > B[maxi[0]]:
            maxi[1] = maxi[0]
            maxi[0] = k
        elif B[k] > B[maxi[1]]:
            maxi[1] = k
            
    # On retourne les deux plus grandes valeurs propres et leurs vecteurs propres associés à l'aide des indices
    return [[B[maxi[0]], B[maxi[1]]], [C[0:C.shape[0], maxi[0]], C[0:C.shape[0], maxi[1]]]]

def _4_5_ValVectPropre_Puissance_Inverse (M):
    # Algorithme de calcul des deux plus grandes valeurs propres et de leurs vecteurs propres associés en utilisant la méthode de la puissance inverse
    # M est la matrice de covariance de la matrice des données socio-économiques sur les pays de la zone euro
    
    # On récupère les valeurs propres et les vecteurs propres de M
    V = nla.eigvals(M)
    
    # On initialise un maximum de valeurs propres au deux premières récupéré (on garde les indices)
    if V[0] > V[1]:
        maxi = [0, 1]
    else:
        maxi = [1, 0]
        
    # On teste tout le reste des valeurs propres pour savoir si il n'ya pas plus grand que celles récupérées
    for k in range(2, V.shape[0]):
        if V[k] > V[maxi[0]]:
            maxi[1] = maxi[0]
            maxi[0] = k
        elif V[k] > V[maxi[1]]:
            maxi[1] = k
            
    # On retourne les deux plus grandes valeurs propres et leurs vecteurs propres associés à l'aide des indices
    return [[V[maxi[0]], V[maxi[1]]], [_PuissanceInverse_(M, V[maxi[0]]), _PuissanceInverse_(M, V[maxi[1]])]]

def _4_5_ValVectPropre_Puissance_Inverse_Et_QR (M):
    # Algorithme de calcul des deux plus grandes valeurs propres et de leurs vecteurs propres associés en utilisant la méthode de la puissance inverse
    # M est la matrice de covariance de la matrice des données socio-économiques sur les pays de la zone euro
    
    # On récupère les valeurs propres et les vecteurs propres de M
    V = _VPAlgoQR_(M)
    
    # On initialise un maximum de valeurs propres au deux premières récupéré (on garde les indices)
    if V[0] > V[1]:
        maxi = [0, 1]
    else:
        maxi = [1, 0]
        
    # On teste tout le reste des valeurs propres pour savoir si il n'ya pas plus grand que celles récupérées
    for k in range(2, V.shape[0]):
        if V[k] > V[maxi[0]]:
            maxi[1] = maxi[0]
            maxi[0] = k
        elif V[k] > V[maxi[1]]:
            maxi[1] = k
            
    # On retourne les deux plus grandes valeurs propres et leurs vecteurs propres associés à l'aide des indices
    return [[V[maxi[0]], V[maxi[1]]], [_PuissanceInverse_(M, V[maxi[0]]), _PuissanceInverse_(M, V[maxi[1]])]]

def _4_5_ValVectPropre_Puissance_Inverse_Et_Gershgorin (M):
    # Algorithme de calcul des deux plus grandes valeurs propres et de leurs vecteurs propres associés en utilisant la méthode de la puissance inverse
    # M est la matrice de covariance de la matrice des données socio-économiques sur les pays de la zone euro
    
    # On récupère les valeurs propres et les vecteurs propres de M
    T = nla.hessenberg(M)
    V = _eigenvalues_ (T, 1e-7)
    
    # On initialise un maximum de valeurs propres au deux premières récupéré (on garde les indices)
    if V[0] > V[1]:
        maxi = [0, 1]
    else:
        maxi = [1, 0]
        
    # On teste tout le reste des valeurs propres pour savoir si il n'ya pas plus grand que celles récupérées
    for k in range(2, V.shape[0]):
        if V[k] > V[maxi[0]]:
            maxi[1] = maxi[0]
            maxi[0] = k
        elif V[k] > V[maxi[1]]:
            maxi[1] = k
            
    # On retourne les deux plus grandes valeurs propres et leurs vecteurs propres associés à l'aide des indices
    return [[V[maxi[0]], V[maxi[1]]], [_PuissanceInverse_(M, V[maxi[0]]), _PuissanceInverse_(M, V[maxi[1]])]]

def _6_coords (M, C):
    # Algorithme de calcul des coordonnées de chaque points à partir des données
    # M est la matrice des données socio-économiques sur les pays de la zone euro
    # C est la matrice de covariance de la matrice des données socio-économiques sur les pays de la zone euro
    
    # On récupère les deux plus grandes valeurs propres de C et leurs vecteurs propres associés
    V = _4_5_ValVectPropre_Initial(C)
    
    # On retourne la matrice des points
    return np.dot(M, np.stack((V[1][0], V[1][1]), axis=-1))

# Visualisation

# On réalise le centrage des données
_1_centrage(A)

# On réalise l'adimentionnement des données
_2_adimention(A)

# On calcule les coordonnées des points à l'aide de la matrice de covariance des données
P = _6_coords(A, _3_covariance(A))

# On affiche les points
plt.scatter (P[:,0], P[:,1])
for i in range(m) :
    plt.annotate (lignes[i], P[i,:])
plt.show ()
